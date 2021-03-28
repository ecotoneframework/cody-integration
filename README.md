# Ecotone - InspectIO Cody Integration

[InspectIO](https://github.com/event-engine/inspectio "InspectIO") can
connect to a coding bot called [Cody](https://github.com/event-engine/inspectio/wiki/PHP-Cody-Tutorial "PHP Cody Tutorial"). 
With its help you can generate working code from an event map. This package connects Cody with Ecotone.

Cody server runs at [http://localhost:3311](http://localhost:3311)

## Installation

Please make sure you have installed [Docker](https://docs.docker.com/install/ "Install Docker")
and [Docker Compose](https://docs.docker.com/compose/install/ "Install Docker Compose").

Install Cody next to your project repository. Let's say project root is in `/home/projects/my_awesome_service` then
you should clone Cody into `/home/projects/cody-integration`:

```bash
$ git clone https://github.com/ecotoneframework/cody-integration.git
$ cd cody-integration
$ # Adjust relative path to your service in .env file
$ cp .env.dist .env
$ composer install
```

## Start Cody Server

To start the Cody server execute the following command. You should be able to connect from *InspectIO* to the Cody server
via the URL `http://localhost:3311`.

```
$ docker-compose up -d --no-recreate
```

## Cody Config

Ecotone Cody Integration ships with preconfigured Cody hooks. If you want to change the default behavior copy and rename
`codyconfig.php.dist` to `codyconfig.php` and adjust as needed.
