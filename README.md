# jms/serializer and custom error codes


This solution contains a pre-configured [Symfony](https://github.com/symfony/symfony) 5 application
with [jms/serializer-bundle](https://github.com/schmittjoh/JMSSerializerBundle) enabled with custom error codes for 
    a JSON API.
    
    
## Setup / Install

This project requires at least PHP `7.2.5`, `ext-json` and [`composer`](https://getcomposer.org/) installed. 

Clone the repository and open a terminal in the repository directory. 

Run:
```bash
composer install
``` 

## Run

You can run a debug server by typing:

```bash
bin/console server:run 127.0.0.1:8081 -vv
```

Open your browser and visit http://localhost:8081.


## How it works

Error codes works thanks to:

- `\App\Entity\Customer` defines some error codes in the `payload` of the `@Assert\*` annotations
- In case of validation errors, the `\App\Serializer\FormErrorHandler` or `\App\Serializer\ConstraintViolationHandler`
copy the validation error to the JSON response that is produced by `jms/serializer`.







