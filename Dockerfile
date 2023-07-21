FROM php:8.1

RUN apt update
RUN apt install -y git unzip

RUN curl -s http://getcomposer.org/installer | php

#
#COPY ./ /opt/bundle/
#
#WORKDIR /opt/bundle
#
#RUN php /composer.phar --dev install
#
#
#CMD ["vendor/bin/phpunit", "--coverage-text"]
