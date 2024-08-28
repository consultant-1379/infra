FROM node:10-alpine
COPY ./package.json ./
RUN npm install

copy . .

EXPOSE 3000
cmd ["node", "server.js"]
