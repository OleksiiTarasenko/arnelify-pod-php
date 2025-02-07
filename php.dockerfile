FROM gcc:14.2.0

WORKDIR /var/www/php

RUN apt-get update -y && apt-get upgrade -y
RUN apt-get install sudo nano libjsoncpp-dev -y

RUN apt-get install -y lsb-release apt-transport-https ca-certificates
RUN curl -sSL https://packages.sury.org/php/README.txt | bash -x

RUN apt-get install -y php8.3 php8.3-cli php8.3-fpm
RUN apt-get install php8.3-mbstring php8.3-pdo php8.3-mysql php8.3-common php8.3-ffi
RUN echo "listen = 0.0.0.0:9000" >> /etc/php/8.3/fpm/pool.d/www.conf

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

CMD ["php-fpm8.3", "-F"]

EXPOSE 9000