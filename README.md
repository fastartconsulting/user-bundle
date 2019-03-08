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
    user_class: FAC\UserBundle\Entity\User

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
      title: FastArt Bundles
      description: This is an awesome bundles app!
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
fos_oauth_server_token:
    resource: "@FOSOAuthServerBundle/Resources/config/routing/token.xml"
    prefix:   /public

fos_oauth_server_authorize:
    resource: "@FOSOAuthServerBundle/Resources/config/routing/authorize.xml"
    prefix:   /private

FACUserBundle:
    resource: '@FACUserBundle/Controller/'
    type: annotation
    prefix: /

app.swagger_ui:
    path: /doc
    methods: GET
    defaults: { _controller: nelmio_api_doc.controller.swagger_ui }
```

- Configure your application's services.yml

```
######################################
# UserBundle
######################################


fos_oauth_server.controller.authorize:
    class: FAC\UserBundle\Controller\AuthorizeController
    public: true
    tags: ['controller.service_arguments']
    arguments:
        $container: '@service_container'

FAC\UserBundle\:
    resource: '../../vendor/fastartconsulting/user-bundle/*'

FAC\UserBundle\Controller\:
    resource: '../../vendor/fastartconsulting/user-bundle/Controller'
    public: true
    tags: ['controller.service_arguments']

FAC\UserBundle\Service\UserService:
    arguments:
        $emailProcess: 'FAC\UserBundle\Service\UserService'
```

- Configure your application's security.yml
```
# To get started with security, check out the documentation:
# https://symfony.com/doc/current/security.html
security:

    encoders:
        FOS\UserBundle\Model\UserInterface: bcrypt

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN

    # https://symfony.com/doc/current/security.html#b-configuring-how-users-are-loaded
    providers:
        in_memory:
            memory: ~
        fos_userbundle:
            id: fos_user.user_provider.username_email

    firewalls:
        service:
            entry_point: FAC\UserBundle\Security\AuthenticationEntryPoint
            access_denied_handler: FAC\UserBundle\Security\AccessDeniedHandler
            pattern: ^/public
            security: false

        api:
            entry_point: FAC\UserBundle\Security\AuthenticationEntryPoint
            access_denied_handler: FAC\UserBundle\Security\AccessDeniedHandler
            pattern: ^/private                         # All URLs are protected
            fos_oauth: true                            # OAuth2 protected resource
            stateless: true                            # Do no set session cookies
            anonymous: false                           # Anonymous access is not allowed
            context: oauth_private

        admin:
            entry_point: FAC\UserBundle\Security\AuthenticationEntryPoint
            access_denied_handler: FAC\UserBundle\Security\AccessDeniedHandler
            pattern: ^/admin                           # All URLs are protected
            fos_oauth: true                            # OAuth2 protected resource
            stateless: true                            # Do no set session cookies
            anonymous: false                           # Anonymous access is not allowed
            context: oauth_admin

        super:
            entry_point: FAC\UserBundle\Security\AuthenticationEntryPoint
            access_denied_handler: FAC\UserBundle\Security\AccessDeniedHandler
            pattern: ^/super                           # All URLs are protected
            fos_oauth: true                            # OAuth2 protected resource
            stateless: true                            # Do no set session cookies
            anonymous: false                           # Anonymous access is not allowed
            context: oauth_super


        # disables authentication for assets and the profiler, adapt it according to your needs
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        main:
            anonymous: ~
            access_denied_handler: FAC\UserBundle\Security\AccessDeniedHandler
            # activate different ways to authenticate

            # https://symfony.com/doc/current/security.html#a-configuring-how-your-users-will-authenticate
            #http_basic: ~

            # https://symfony.com/doc/current/security/form_login_setup.html
            #form_login: ~

        oauth_token:                                   # Everyone can access the access token URL.
            pattern: ^/oauth/v2/token
            security: false

        oauth_authorize:
            pattern: ^/oauth/v2/auth
            form_login:
                provider: fos_userbundle
                check_path: fos_user_security_login
                login_path: fos_user_security_check
                csrf_token_generator: security.csrf.token_manager
            anonymous: true
            context: test_connect

        #api:
        #    pattern: ^/
        #    fos_oauth: true                            # OAuth2 protected resource
        #    stateless: true                            # Do no set session cookies
        #    anonymous: false                           # Anonymous access is not allowed

    access_control:
        - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oauth/v2/, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/public, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/private, role: ROLE_USER }
        - { path: ^/admin, role: ROLE_ADMIN }
        - { path: ^/super, role: ROLE_SUPER_ADMIN }

```

- Run Server and Enjoy ;) 

After 
```
php bin/console doctrine:schema:update --force
php bin/console server:start
```

you can see the API's on 127.0.0.1/doc

Our integrations
------------

License
-------

This bundle is under the MIT license.
