package adpportal

import io.gatling.core.Predef._
import io.gatling.http.Predef._
import scala.concurrent.duration._
import java.util.concurrent.TimeUnit
import com.typesafe.config._
import io.gatling.core.feeder._
import scala.concurrent.duration.DurationInt
import java.lang.Long

class BasicSimulation extends Simulation {

  val envName = System.getProperty("env_name")
  if (envName == null) {
    System.out.println("Please provide a valid environment")
    System.exit(1);
  }

  val feederOverride = System.getProperty("feeder_override")

  val token = "Bearer " + System.getProperty("user_auth_token")

  val conf = ConfigFactory.load("env.conf").getConfig(envName)
  val baseUrl = conf.getString("baseUrl")
  
  val msFeeder = conf.getString("msFeeder")
  var feederFileRandom = msFeeder;
  if (feederOverride == null || feederOverride == "config") {
    // Default to the config file setting
  } else {
    // Use the specified override file
    feederFileRandom = feederOverride
  }
  val wpUrl = conf.getString("wpUrl")

  val feederStep1 = csv("feeder_marketplace.csv").random
  val feederStep2 = csv(feederFileRandom).random
  val feederStep3 = csv("feeder_administration.csv").random
  val feederStepWP1 = jsonFile("feeder_articles.json").random

  val httpProtocolBE = http
    .baseUrl(baseUrl) // Here is the root for all relative URLs
    .acceptHeader("text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8") // Here are the common headers
    .doNotTrackHeader("1")
    .acceptLanguageHeader("en-US,en;q=0.5")
    .acceptEncodingHeader("gzip, deflate")
    .userAgentHeader("Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:16.0) Gecko/20100101 Firefox/16.0")  

  val httpProtocolWP = http
    .baseUrl(wpUrl) // Here is the root for all relative URLs
    .acceptHeader("text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8") // Here are the common headers
    .doNotTrackHeader("1")
    .acceptLanguageHeader("en-US,en;q=0.5")
    .acceptEncodingHeader("gzip, deflate")
    .userAgentHeader("Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:16.0) Gecko/20100101 Firefox/16.0")  

  def requestThis(requestName:String, url:String, article:String, mode:String) = {
    doSwitch(mode)(
      "anonymousGet" -> exec(
        http(requestName)
        .get(url)
        .header("Authorization", token)
      ),
      "adminGet" -> exec(
        http(requestName)
        .get(url)
        .header("Authorization", token)
      ),
      "adminPost" -> exec(
        http(requestName)
        .post(url)
        .header("Authorization", token)
      ),
      "wpGet" -> exec(
        http(requestName)
        .get(url)
        .header("Authorization", token)
      )
    )
  }

  def requestThisArticle(articleSlug:String, articleType:String, parentSlugArray:Any) = {
    http("[WP] User reads random article")
    .get("/wpcontent/fetchArticleValidatePath?articleSlug=${articleSlug}&articleType=${articleType}&parentSlugArray=[%22${parentSlugArray}%22]")
    .header("Content-Type", "application/json")
    .header("Accept", "application/json")
    .header("Authorization", token)
  }

  val scenarioBackend = scenario("ADP Portal Backend")
    .pause(1, 2)
    .feed(feederStep1)
    .exec(requestThis("[BE] User opens/searches in Marketplace", "/${endpoint}", "", "anonymousGet"))
    .pause(1, 2)
    .feed(feederStep2)
    .exec(requestThis("[BE] Anonymous User open random Microservice", "/microservice/${slug}", "", "anonymousGet"))
    .pause(1, 2)
    .feed(feederStep3)
    .exec(requestThis("[BE] Admin User read list of Microservices", "/${endpoint}", "", "adminPost"))

  val scenarioWordPress = scenario("ADP WordPress")
    .pause(1, 2)
    .exec(requestThis("[WP] User reads Menu (Starting Frontend)", "/wordpress/menus/main?ts=20200031085130", "", "wpGet"))
    .pause(1, 2)
    .exec(requestThis("[WP] User reads HighLights (Home)", "/wordpress/menus/highlights?ts=20200031085030", "", "wpGet"))
    .pause(1, 2)
    .feed(feederStepWP1)
    .exec(requestThisArticle("${articleSlug}", "${articleType}", "${parentSlugArray}"))

  val usersPerSec: Double = Integer.getInteger("load").toDouble
  val secondsTime: FiniteDuration = new FiniteDuration(Long.getLong("time"), TimeUnit.SECONDS)

  setUp(
    scenarioBackend.inject(
      constantUsersPerSec(usersPerSec) during (secondsTime) randomized
    ).protocols(httpProtocolBE),
    scenarioWordPress.inject(
      constantUsersPerSec(usersPerSec) during (secondsTime) randomized
    ).protocols(httpProtocolBE)
  )
}
