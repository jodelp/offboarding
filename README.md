git clone https://github.com/jodelp/offboarding.git offboarding

docker build -t offboarding-image -f Dockerfile.dev .

docker run -p 8020:80 -dit --name offboarding_web --mount type=bind,source="$(pwd)",target=/var/www/html offboarding-image

docker exec -it offboarding_web bash

php composer.phar install

php composer.phar install


//run the command below for database tables
bin/cake migrations migrate

//run the following for data seeding
php vendor/bin/phinx seed:run -s UserSeeder
php vendor/bin/phinx seed:run -s DepartmentSeeder
php vendor/bin/phinx seed:run -s FormSeeder
php vendor/bin/phinx seed:run -s SubFormsSeeder