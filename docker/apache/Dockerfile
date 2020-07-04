ARG APACHE_VERSION="2.4.32"
FROM httpd:${APACHE_VERSION:+${APACHE_VERSION}-}alpine
LABEL maintainer="Cristian Pini"
ENV TZ=Europe/London
RUN apk update; \
    apk upgrade; \
    apk add --no-cache tzdata; 
RUN mkdir -p /usr/local/apache2/conf/ssl-certs
#COPY /usr/share/zoneinfo/$TZ /etc/localtime 
#RUN echo $TZ > /etc/timezone;

# Copy apache vhost file to proxy php requests to php-fpm container
COPY demo.apache.conf /usr/local/apache2/conf/demo.apache.conf
COPY dev.local.crt /usr/local/apache2/conf/ssl-certs/dev.local.crt
COPY dev.local.key /usr/local/apache2/conf/ssl-certs/dev.local.key
COPY localCA.pem /usr/local/apache2/conf/ssl-certs/localCA.pem
RUN echo "Include /usr/local/apache2/conf/demo.apache.conf" \
    >> /usr/local/apache2/conf/httpd.conf;