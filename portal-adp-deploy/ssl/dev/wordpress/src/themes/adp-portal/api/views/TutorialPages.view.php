<?php
/**
 * Wordpress Admin Tutorials
 *
 * PHP version 7.1
 *
 * @category WP_Admin_Tutorials
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Views;

require_once __DIR__.'/../models/PagePostTutorial.model.php';

use api\Models\PagePostTutorialModel;

/**
 * Wordpress Admin Settings View
 *
 * @category WP_Admin_Tutorials
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class TutorialPagesView {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Renders the tutorial page date content field
     * which is used to mark a tutorial updated for user progress
     * 
     * @param object $post the given post
     * 
     * @return void
     * @author Cein
     */
    function renderTutorialPageDateContentMetaBox($post): void {
        $dateContent = '';
        if (isset($post->ID)) {
            $dateContent = PagePostTutorialModel::getPostPageTutorialMetaById($post->ID, 'adp_portal_wp_date_content');
        }
        ?>
            <script>
                /**
                 * Toggles the checkbox value with the current date or a blank string
                 * @auth Cein
                 */
                function toggleDateContentVal() {
                    const checkBoxField = document.getElementById('adp_portal_wp_date_content_checkbox');
                    const checkBoxLabel = document.getElementById('adp_portal_wp_date_content_input_label'); 
                    const valueHiddenField = document.getElementById('adp_portal_wp_date_content_input');
                    const unsetDateContentBtn = document.getElementById('unsetDateContentBtnContainer');

                    if(checkBoxField.checked) {
                        const todaysDate = new Date();
                        const newDateContent = todaysDate.toString();
                        valueHiddenField.value = newDateContent;
                        checkBoxLabel.innerHTML = ` ${newDateContent}.`;
                        unsetDateContentBtn.style.display = '';
                    } else {
                        originalDateContent = valueHiddenField.dataset.origdatecontent;
                        if (originalDateContent !== '') {
                            valueHiddenField.value = originalDateContent;
                            checkBoxLabel.innerHTML = ` ${originalDateContent}.`;
                            unsetDateContentBtn.style.display = '';
                        } else {
                            valueHiddenField.value = '';
                            checkBoxLabel.innerHTML = 'not set.';
                            unsetDateContentBtn.style.display = 'none';
                        }
                    }
                }

                /**
                 * Unset the date content
                 * @auth Cein
                 */
                function unsetDateContent() {
                    const checkBoxField = document.getElementById('adp_portal_wp_date_content_checkbox');
                    const checkBoxLabel = document.getElementById('adp_portal_wp_date_content_input_label'); 
                    const valueHiddenField = document.getElementById('adp_portal_wp_date_content_input');
                    const unsetDateContentBtn = document.getElementById('unsetDateContentBtnContainer');

                    valueHiddenField.value = '';
                    checkBoxLabel.innerHTML = 'not set.';
                    unsetDateContentBtn.style.display = 'none';
                    checkBoxField.checked = false;
                }
            </script>
            <p>Any user who has completed this tutorial before the set date and time will be notified of this update.</p>
            
            <input type="checkbox"
                   id="adp_portal_wp_date_content_checkbox"
                   onclick="toggleDateContentVal()"
                   />
            <input type="hidden"
                   name="adp_portal_wp_date_content"
                   id="adp_portal_wp_date_content_input"
                   data-origdatecontent="<?php echo $dateContent ?>"
                   value="<?php echo $dateContent ?>" />


            <label for="adp_portal_wp_date_content_checkbox" > <b>Set</b></label>
            <br/><br/>
            <b>Date: </b> 
            <span id="adp_portal_wp_date_content_input_label">
                <?php 
                    echo ($dateContent !== '' ? "$dateContent.": 'not set.');
                ?>
            </span>
                <?php
                if ($dateContent !== '') {
                    ?><div><br/><button onclick="unsetDateContent()" type="button" id="unsetDateContentBtnContainer">Clear</button></div><?php
                }
                ?>
        <?php
    }
}