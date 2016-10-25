#Email Sender Docker-symfony project

# Installation

    After download:

    Make sure you adjust `database` in `symfony/app/parameters.yml` 
    (after you build docker image you can user alias 'db' or 'd6442a4eec51' for db_host)
    Make sure you adjust `swiftmailer:` in `symfony/app/config.yml`

    Make sure you used 'doctrine:database:create' and 'doctrine:schema:update --force'

## Running

    Run the Docker environment using

    $ docker-build
    $ docker-compose up -d
  
    You can check IP with

    $ docker inspect $(docker ps -f name=nginx -q) | grep IPAddress

## Using
   
    1. First you need to register user (make sure you have table symfony.fos_user in DB)
    2. You can send emails in format over API with Basic Auth (username and password - creds of registered user) - 
       make sure you have symfony.email table in DB
    
        http://yourDomain/api/emails
	    [
		{
		"from": "…",
		"to": "…",
		"subject": "…",
		"message": "…"
		},
		{
		"from": "…",
		"to": "…",
		"subject": "…",
		"message": "…"
		}
	    ]

    3. You can get emails written in the DB over API with Basic Auth (username and password - creds of registered user)

        http://yourDomain/api/list

