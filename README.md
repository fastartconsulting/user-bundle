FastART Consulting 
=============

UserBundle v1.0 STABLE
=============

This bundle provides a configuration of RESTful API's for User

Integrated with:
- FOS oAuth Server Bundle - friendsofsymfony/oauth-server-bundle
- FOS Rest Bundle - friendsofsymfony/rest-bundle
- FOS User Bundle - friendsofsymfony/user-bundle
- JMS Serializer Bundle - jms/serializer-bundle
- Nelmio CORS Bundle - nelmio/cors-bundle


Documented with:
- Nelmio API DOC Bundle - nelmio/api-doc-bundle


Note
----

FACUserBundle now in Alpha. Soon stable.

Documentation
-------------
- Setup
- Configuration

Setup
------------
- **A) Download the Bundle**

Open a command console, enter your project directory and execute the following command to download the latest stable version of this package:

**`composer require fastartconsulting/user-bundle`**

- **B) Add Bundles in AppKernel.php**

Open the file AppKernel.php located inside /app folder and add the following lines:

```
new FOS\RestBundle\FOSRestBundle(),
new FOS\UserBundle\FOSUserBundle(),
new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
new JMS\SerializerBundle\JMSSerializerBundle(),
new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
new FAC\UserBundle\FACUserBundle(),
```

Configuration
------------
- Configure your application's parameters.yml and add valid values for:
```
mailer_user: no-reply@fastartconsulting.com
sender_name: FACUserBundle
```

- Configure your application's config.yml

```
fos_rest:
    routing_loader:
        default_format: json                            # All responses should be JSON formated
        include_format: false                           # We do not include format in request, so that all responses
    view:
        view_response_listener: force
        force_redirects:
            html: true
        formats:
            json: true
            xml: true
    format_listener:
        rules:
            - { path: ^/, priorities: [ json, xml ], fallback_format: json, prefer_extension: true }
    body_converter:
        enabled: true
    #    validate: true
    #    validation_errors_argument: validationErrors
    param_fetcher_listener: true

fos_user:
    db_driver: orm
    firewall_name: api                                  # Seems to be used when registering user/reseting password,
      # but since there is no "login", as so it seems to be useless in
    # our particular context, but still required by "FOSUserBundle"
    user_class: FAC/UserBundle\Entity\User

    # This config is used to user confirmation by sending email
    registration:
        confirmation:
            enabled: true
            template:   email/registration.email.twig

    resetting:
        email:
            template:   email/password_resetting.email.twig

    service:
        mailer: fos_user.mailer.twig_swift
        user_manager: fos_user.user_manager.default
    from_email:
        address:     "%mailer_user%"
        sender_name: "%sender_name%"

fos_oauth_server:
    db_driver:           orm
    client_class:        FAC\UserBundle\Entity\Client
    access_token_class:  FAC\UserBundle\Entity\AccessToken
    refresh_token_class: FAC\UserBundle\Entity\RefreshToken
    auth_code_class:     FAC\UserBundle\Entity\AuthCode
    service:
        #user_provider: fos_user.user_manager             # This property will be used when valid credentials are given to load the user upon access token creation
        user_provider: fos_user.user_provider.username_email

nelmio_api_doc:
  documentation:
    info:
      title: UserBundle
      description: This is an awesome userbundle app!
      version: 1.0.0
  areas:
    path_patterns:
      - ^/public(?!/doc$)
      - ^/private(?!/doc$)
      - ^/admin(?!/doc$)
      - ^/super(?!/doc$)
```

- Configure your application's routing.yml

```
FACUserBundle:
    resource: '@FACUserBundle/Controller/'
    type: annotation
    
app.swagger_ui:
    path: /doc
    methods: GET
    defaults: { _controller: nelmio_api_doc.controller.swagger_ui }
```

- Configure your application's services.yml

```
fos_oauth_server.controller.token:
        class: FAC\UserBundle\Controller\TokenController
        public: true
        tags: ['controller.service_arguments']
        arguments:
            $container: '@service_container'


fos_oauth_server.controller.authorize:
    class: FAC\UserBundle\Controller\AuthorizeController
    public: true
    tags: ['controller.service_arguments']
    arguments:
        $container: '@service_container'
```

- Run Server and Enjoy ;) 

After 
```
php bin/console server:start
```

you can see the API's on 127.0.0.1/doc

License
-------

This bundle is under the MIT license.
