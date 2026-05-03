FROM node:24-alpine AS build

WORKDIR /app


COPY App/package*.json ./


RUN rm -rf node_modules && npm install


COPY App/ . 


RUN rm -rf App/node_modules

RUN npm run build-only

FROM nginx:1.27-alpine
RUN apk add --no-cache curl

COPY nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80
