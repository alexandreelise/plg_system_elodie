# System - Elodie

Add sparse fieldset as JSONAPI v1.0 Specs shows it to Joomla! 4 using only one system plugin

------------------------------------------------------------------------

## USAGE

> English: 

1. Download and install the plugin on your Joomla! 4 website (make a backup first just in case)
2. Make GET request call to any  Joomla! 4 Web Services GET endpoint using any HTTP Client it should work. For example (Curl, Postman, Guzzle, Joomla! Http Client, etc...) with an additional query string.
3. Enjoy. Give your feedback on potential improvement and issues

For users endpoint:

Users Collection:
```

curl -X GET -H "X-Joomla-Token: YOUR-TOKEN" -H "Accept: application/vnd.api+json; charset=utf-8" -H "Content-Type: application/vnd.api+json; charset=utf-8" --url "https://example.org/api/index.php/v1/users?fields[users]=id,name"
```

Single User (id:1234)

```
curl -X GET -H "X-Joomla-Token: YOUR-TOKEN" -H "Accept: application/vnd.api+json; charset=utf-8" -H "Content-Type: application/vnd.api+json; charset=utf-8" --url "https://example.org/api/index.php/v1/users/1234?fields[users]=id,name"

```

For articles endpoint:

Article Collection
```

curl -X GET -H "X-Joomla-Token: YOUR-TOKEN" -H "Accept: application/vnd.api+json; charset=utf-8" -H "Content-Type: application/vnd.api+json; charset=utf-8" --url "https://example.org/api/index.php/v1/content/articles?fields[articles]=id,title,alias,featured,state,access"

```

Single Article (id:5678)

```
curl -X GET -H "X-Joomla-Token: YOUR-TOKEN" -H "Accept: application/vnd.api+json; charset=utf-8" -H "Content-Type: application/vnd.api+json; charset=utf-8" --url "https://example.org/api/index.php/v1/content/articles/5678?fields[articles]=id,title,alias,featured,state,access"

```


> Français:

1. Téléchargez et installer le plugin sur votre site Joomla! 4 (faite une sauvegarde avant juste au cas où)
2. Appelez avec une requete HTTP GET n'importe quel point d'entrée des Web Services Joomla! 4 en utilisant n'importe quel client HTTP de votre choix. Par exemple (Curl, Postman, Guzzle, Joomla! Http Client, etc...) avec un paramètre en plus dans l'url
3. Donnez vos impressions, retours sur l'utilisation, amelioration potientielle et éventuelles erreurs.

Pour le point d'entrée des utilisateurs:

Liste utilisateurs:
```

curl -X GET -H "X-Joomla-Token: YOUR-TOKEN" -H "Accept: application/vnd.api+json; charset=utf-8" -H "Content-Type: application/vnd.api+json; charset=utf-8" --url "https://example.org/api/index.php/v1/users?fields[users]=id,name"
```

Un seul utilisateur (id:1234)

```
curl -X GET -H "X-Joomla-Token: YOUR-TOKEN" -H "Accept: application/vnd.api+json; charset=utf-8" -H "Content-Type: application/vnd.api+json; charset=utf-8" --url "https://example.org/api/index.php/v1/users/1234?fields[users]=id,name"

```

Pour le point d'entrée des articles

Liste d'articles
```

curl -X GET -H "X-Joomla-Token: YOUR-TOKEN" -H "Accept: application/vnd.api+json; charset=utf-8" -H "Content-Type: application/vnd.api+json; charset=utf-8" --url "https://example.org/api/index.php/v1/content/articles?fields[articles]=id,title,alias,featured,state,access"

```

Un seul article (id:5678)

```
curl -X GET -H "X-Joomla-Token: YOUR-TOKEN" -H "Accept: application/vnd.api+json; charset=utf-8" -H "Content-Type: application/vnd.api+json; charset=utf-8" --url "https://example.org/api/index.php/v1/content/articles/5678?fields[articles]=id,title,alias,featured,state,access"

```

## JSON-API SPEC

[Official specification on how sparse fieldsets should work](https://jsonapi.org/format/#fetching-sparse-fieldsets)

## INFOS

> English: [Click here to get in touch](https://github.com/mralexandrelise/mralexandrelise/blob/master/community.md "Get in touch")

> Français: [Cliquez ici pour me contacter](https://github.com/mralexandrelise/mralexandrelise/blob/master/community.md "Me contacter")
