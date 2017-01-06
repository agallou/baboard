# baboard

A dashbard for your docker-compose dev environment.

## Features

A simple listing of the projets curently running locally in order to open them easily.

Each service that have a port binding with a name. They will be grouped by projet and category.

Here is an example of what it looks like ;

PHOTO.

## Install it

### Via docker

Run :
```
docker run 
```

## Use it

### baboard.container.disabled

`boolean` : do not display the service on baboard.

### baboard.container.name

`string` : service name. Defaults to ``. Examples : `Mailcatcher`, `Front`, `Back`, `FR`, `EN`, `Redis Commander`.
 
### baboard.project.name

`string` : project name. Defaults to ``. Examples : `atoum website`, `atoum documentation`, `afup.org`, `aperophp.net`, `myProject`. 

### baboard.project.category

`string` : category name. By defaut, the container does not have a category. Examples `atoum`, `afup`, `myCompagny`.

## License

TODO

