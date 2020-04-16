up: docker-up app-clear app-ready
down: docker-down app-clear
restart: docker-down docker-up
init: docker-down-clear app-clear docker-pull docker-build docker-up app-init
test: app-test
test-coverage: app-test-coverage
test-unit: app-test-unit
test-unit-coverage: app-test-unit-coverage

docker-up:
	docker-compose up -d --scale queue-worker=3

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

docker-php-cli:
	docker-compose exec php-cli /bin/bash

assets-watch:
	docker-compose exec node npm run watch

app-clear:
	docker run --rm -v ${PWD}:/app --workdir=/app alpine rm -f .ready

app-composer-install:
	docker-compose run --rm app-php-cli composer install

app-assets-install:
	docker-compose run --rm app-node yarn install
	docker-compose run --rm app-node npm rebuild node-sass

app-oauth-keys:
	docker-compose run --rm app-php-cli mkdir -p var/oauth
	docker-compose run --rm app-php-cli openssl genrsa -out var/oauth/private.key 2048
	docker-compose run --rm app-php-cli openssl rsa -in var/oauth/private.key -pubout -out var/oauth/public.key
	docker-compose run --rm app-php-cli chmod 644 var/oauth/private.key var/oauth/public.key

app-wait-db:
	until docker-compose exec -T app-postgres pg_isready --timeout=0 --dbname=app ; do sleep 1 ; done

app-migrations:
	docker-compose run --rm app-php-cli php bin/console doctrine:migrations:migrate --no-interaction

app-fixtures:
	docker-compose run --rm app-php-cli php bin/console doctrine:fixtures:load --no-interaction

app-ready:
	docker run --rm -v /home/truehero/projects/yandex-loader:/app --workdir=/app alpine touch .ready

app-assets-dev:
	docker-compose run --rm app-node npm run dev

app-test:
	docker-compose run --rm app-php-cli php bin/phpunit

app-test-coverage:
	docker-compose run --rm app-php-cli php bin/phpunit --coverage-clover var/clover.xml --coverage-html var/coverage

app-test-unit:
	docker-compose run --rm app-php-cli php bin/phpunit --testsuite=unit

app-test-unit-coverage:
	docker-compose run --rm app-php-cli php bin/phpunit --testsuite=unit --coverage-clover var/clover.xml --coverage-html var/coverage

build-production:
	docker build --pull --file=manager/docker/production/nginx.docker --tag ${REGISTRY_ADDRESS}/app-nginx:${IMAGE_TAG} manager
	docker build --pull --file=manager/docker/production/php-fpm.docker --tag ${REGISTRY_ADDRESS}/app-php-fpm:${IMAGE_TAG} manager
	docker build --pull --file=manager/docker/production/php-cli.docker --tag ${REGISTRY_ADDRESS}/app-php-cli:${IMAGE_TAG} manager
	docker build --pull --file=manager/docker/production/postgres.docker --tag ${REGISTRY_ADDRESS}/app-postgres:${IMAGE_TAG} manager
	docker build --pull --file=manager/docker/production/redis.docker --tag ${REGISTRY_ADDRESS}/app-redis:${IMAGE_TAG} manager
	docker build --pull --file=centrifugo/docker/production/centrifugo.docker --tag ${REGISTRY_ADDRESS}/centrifugo:${IMAGE_TAG} centrifugo

push-production:
	docker push ${REGISTRY_ADDRESS}/app-nginx:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/app-php-fpm:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/app-php-cli:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/app-postgres:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/app-redis:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/centrifugo:${IMAGE_TAG}

deploy-production:
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'rm -rf docker-compose.yml .env'
	scp -o StrictHostKeyChecking=no -P ${PRODUCTION_PORT} docker-compose-production.yml ${PRODUCTION_HOST}:docker-compose.yml
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "REGISTRY_ADDRESS=${REGISTRY_ADDRESS}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "IMAGE_TAG=${IMAGE_TAG}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_APP_SECRET=${MANAGER_APP_SECRET}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_DB_PASSWORD=${MANAGER_DB_PASSWORD}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_REDIS_PASSWORD=${MANAGER_REDIS_PASSWORD}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_MAILER_URL=${MANAGER_MAILER_URL}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_OAUTH_FACEBOOK_SECRET=${MANAGER_OAUTH_FACEBOOK_SECRET}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "STORAGE_BASE_URL=${STORAGE_BASE_URL}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "STORAGE_FTP_HOST=${STORAGE_FTP_HOST}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "STORAGE_FTP_USERNAME=${STORAGE_FTP_USERNAME}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "STORAGE_FTP_PASSWORD=${STORAGE_FTP_PASSWORD}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "CENTRIFUGO_WS_HOST=${CENTRIFUGO_WS_HOST}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "CENTRIFUGO_API_KEY=${CENTRIFUGO_API_KEY}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "CENTRIFUGO_SECRET=${CENTRIFUGO_SECRET}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'docker-compose pull'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'docker-compose up --build -d'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'until docker-compose exec -T app-postgres pg_isready --timeout=0 --dbname=app ; do sleep 1 ; done'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'docker-compose run --rm app-php-cli php bin/console doctrine:migrations:migrate --no-interaction'
