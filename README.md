# App SMS Auth

This is Symfony application for authentication with sending SMS codes.

To prevent spam, I used rate limit. Requests are checked by a combination of IP and route name.
A user can send an SMS code once every 3 minutes (with a maximum of 3 attempts per login).
After the third attempt, the ability to send an SMS is blocked for 6 hours.

Currently, this application can simulate sending SMS only (we can't login in the system)

## Installation

### Preparation

The build is based on Docker, so you should [install](https://docs.docker.com/compose/install/) it on your local machine

If you use Windows, it's better to [install WSL](https://documentation.ubuntu.com/wsl/en/latest/guides/install-ubuntu-wsl2/)

Also, we need [Postman Desktop](https://www.postman.com/downloads/) for testing API endpoint

### Clone the repository

In the command line, run the following commands
```bash
git clone https://github.com/VBaldych/app-sms-auth.git
cd app-sms-auth
```

### Run local build

If you run the build for the first time, initialize it using command below for building containers,
install Composer dependencies and running migration
```bash
make init
```

To start a local build run
```bash
make start
```

### Open application
Now we can go to application auth page [http://127.0.0.1:8080/auth](http://127.0.0.1:8080/auth).
For debugging purposes, I outputted JSON responses in form template
Enjoy!

### Test API
Actually, auth form uses API endpoint for processing user, so no need to test API endpoints additionally :)
But, if you want to test API endpoint manually, let's make a POST requests in Postman:
1. Put the URL `http://127.0.0.1:8080/api/auth/send-sms`
2. Put Request Body (JSON):
```json
{
  "phone": "+380500000000"
}
```
  
As a response, you can see SMS auth code or error

## Code analysis
You can take a look I use greate tool Rector for code analysis. It helps to keep your code modern & fancy
For analyzing code with Rector, run it inside PHP container

```bash
make php-cli
vendor/bin/rector process --dry-run
```

## How we can improve it

For better preventing spam we can use following packages:
- [https://github.com/misd-service-development/phone-number-bundle](https://github.com/misd-service-development/phone-number-bundle)
- [https://github.com/google/recaptcha](https://github.com/google/recaptcha)

For better rate limiting we can check IP & route name together with phnoe number

For getting more information about user device, we can use `$_SERVER['HTTP_USER_AGENT']` PHP variable or package [https://github.com/acsiomatic/device-detector-bundle](https://github.com/acsiomatic/device-detector-bundle)

For historical analysis, we can analyze past SMS requests to detect patterns of abuse, such as multiple
requests in a short time frame.

Also, we can use Symfony Messenger for sending SMS asynchronous. Currently, I decided to keep it simple

## Time spent
For this task I spent 17 hours. It's a complex of:
- Task analysis
- Planning architecture
- Configure local environment
- Specific investigations
- Implementation
- Testing
- Code analysis
- Writing documentation
