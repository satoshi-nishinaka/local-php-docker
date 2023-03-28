FROM php:8.1-apache
RUN apt-get update -y && \
    apt-get install -y wget vim
# Set local time
RUN ln -sf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime
COPY distfiles/sites-available/000-default.conf /etc/apache2/sites-available/
# Copy to docker container
RUN mkdir /app
WORKDIR /app
COPY app /app/
# Set alias
RUN echo 'alias ll="ls -lah"' >> ~/.bashrc
EXPOSE 80
