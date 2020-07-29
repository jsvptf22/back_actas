FROM node:current-slim

WORKDIR /usr/src/app

ENV HTTPS_PROTOCOL 1
COPY . .

RUN npm install
EXPOSE 8080

CMD ["sh", "-c", "npm start --httpsProtocol=${HTTPS_PROTOCOL} --port=8080"]