FROM node:current-slim

WORKDIR /usr/src/app

ENV SAIA_DOMAIN http://localhost/saia_2020/saia/
COPY . .

RUN npm install
EXPOSE 8080
CMD ["sh", "-c", "npm start --saiaRoute=$SAIA_DOMAIN --port=8080"]

