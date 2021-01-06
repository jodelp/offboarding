# Workbench Microservice

Workbench microservice
Workbench microservice cater all API need of Workbench app

## Requirements

1. Git
2. Docker Engine
2. PHP 7.0 or greater
3. CakePHP version 3.x or higher
4. ElasticSearch Server
5. MariaDB


## Installation

1. Check out or clone our current code base.

```
    git clone  https://<GitHub_username>@github.com/cs-devs/cs-workbench-microservice.git <dir_name>
```
Legend:
    <dir_name> is a folder name you can change this to whatever you like

2. Make sure you have Docker installed in your hosts. If not please go to Docker official website and download the
[Docker Community Edition](https://www.docker.com/community-edition#/download)

3. Build the Docker image from the top level directory of the source tree. cd to the source directory;

```
cd /path/to/folder_name
docker build -t workbench-ms-image -f Dockerfile.dev .
```
Docker will take care of the following on software dependencies:

* Install the nginx web-server application
* Install configuration files
* Install php7.2 FPM and PHP relevant modules
    - mbstring
    - intl
    - zip
    - xml
    - mysql/mariadb client
    - curl
* Install Git for composer

4. Launch the container. You can set your HTTP is port to any values you prefer. Example 8019 on localhost. After
launching the container bash/shell is available immediately. And if you exit the container it will remove itself from the
container list.

```
docker run -p 8022:80 -dit --name workbench-ms_web --mount type=bind,source="$(pwd)",target=/var/www/html workbench-ms-image
```

5. You need to install the CakePHP framework dependencies via composer

```
docker exec -it workbench-ms_web bash
php composer.phar install
php composer.phar install # calling it twice in order to reach the folder permission routine
```

6. Verify that you can load the web-application from your browser. Open your browser and to go
http://localhost:8022/ this should load our web-application on your browser. However we still need to configure it.


## Configuration

1. Update the app.php file accordingly:

```
    DATABASE_URL="mysql://<db_username>:<db_password>@<localhost>/<db_name>?encoding=utf8&timezone=UTC&cacheMetadata=true&quoteIdentifiers=false&persistent=false"
    i.e.
    DATABASE_URL="mysql://username:secret@172.17.0.4/database_name?encoding=utf8&timezone=UTC&cacheMetadata=true&quoteIdentifiers=false&persistent=false"
```

2. Let run our database migration scripts. Ensure that you have created a database for this web-application and have set the
datasource propely as stated above.


```
docker exec -it workbench-ms_web bash

bin/cake migrations migrate
```

## Update
TBA


## Layout
TBA
