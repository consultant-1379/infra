const express = require('express')
const app = express()
const port = 3000

app.get('/', (req, res) => {
  res.send('Default Service')
})

app.listen(port, () => {
  console.log(`Default Service listening on :${port}`)
})