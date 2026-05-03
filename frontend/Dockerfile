FROM node:24-alpine AS build

WORKDIR /app

# Копіюємо тільки файли залежностей
COPY App/package*.json ./

# ВИДАЛЯЄМО можливі залишки та встановлюємо чисті залежності для Linux
RUN rm -rf node_modules && npm install

# Копіюємо весь інший код
COPY App/ . 

# Додатково видаляємо node_modules з копійованої папки App, 
# щоб вони не перетерли встановлені вище
RUN rm -rf App/node_modules

RUN npm run build-only

FROM nginx:1.27-alpine
RUN apk add --no-cache curl

COPY nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80
