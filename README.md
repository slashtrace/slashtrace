# SlashTrace - Awesome error handler

[![Screenshot](https://i.imgur.com/pt4jlYX.png)](https://slashtrace.com/demo.php)

---

SlashTrace is, at its core, an error and exception handler. You hook it into your error handling routine (or let it set itself up to catch all errors and exceptions), and it captures and displays a lot of nice information about your errors. It does this for normal browser requests, but also for [AJAX](https://i.imgur.com/BnvCp4N.png) and the [CLI](https://i.imgur.com/GA7tS0T.png).

When you're done with local debugging, you can configure SlashTrace to send errors to dedicated error reporting services, like [Sentry](https://sentry.io/), [Raygun](https://raygun.com/), and [Bugsnag](https://www.bugsnag.com/).

## Usage

1. Install using Composer:

   ```
   composer require slashtrace/slashtrace
   ```
    
2. Capture errors:

   ```PHP
   use SlashTrace\SlashTrace;
   use SlashTrace\EventHandler\DebugHandler;

   $slashtrace = new SlashTrace();
   $slashtrace->addHandler(new DebugHandler());
   $slashtrace->register();
   ```

   Alternatively, you can explicitly handle exceptions:

   ```PHP
   try {
       // Your code
   } catch (Exception $exception) {
       $slashtrace->handleException($exception);
   }
   ``` 

## Handlers

SlashTrace comes bundled with the DebugHandler used in the example above, but you will usually want to set it up to send errors to an error tracking service when running in production. Currently, there are handlers implemented for the following providers, and more are on the way. Click each link to view the usage documentation:

- [Sentry](https://github.com/slashtrace/slashtrace-sentry)
- [Raygun](https://github.com/slashtrace/slashtrace-raygun)
- [Bugsnag](https://github.com/slashtrace/slashtrace-bugsnag)

## Capturing additional data

Besides the complex error information that SlashTrace captures out of the box, you can attach other types of data to each report. This is especially useful when using one of the external handlers above. 

This way, SlashTrace acts like an abstraction layer between you and these providers, and normalizes the data that you provide into a single format. This helps you to avoid vendor lock-in and lets you switch error reporting providers simply by switching the handler.

### Capturing user data

If you want to attach information about the affected user, you can do so like this:

```PHP
use SlashTrace\Context\User;

$user = new User();
$user->setId(12345); 
$user->setEmail('pfry@planetexpress.com');
$user->setName('Philip J. Fry');

$slashtrace->addUser($user);
```

Note that a user needs at least an ID or an email. The name is completely optional.

This feature corresponds to the respective implementations in each error tracker:

- [Sentry - Capturing the User](https://docs.sentry.io/learn/context/?platform=javascript#capturing-the-user)
- [Raygun - User Tracking](https://raygun.com/docs/workflow/user-tracking)
- [Bugsnag - Identifying users](https://docs.bugsnag.com/platforms/php/other/#identifying-users)


### Recording breadcrumbs

Sometimes a stack trace isn't enough to figure out what steps lead to an error. To this end, SlashTrace let's you record breadcrumbs during execution:

```PHP
$slashtrace->recordBreadcrumb("Router loaded");
$slashtrace->recordBreadcrumb("Matched route", [
    "controller" => "orders",
    "action" => "confirm",
]);
```

Relevant tracker docs:

- [Sentry - Breadcrumbs in PHP](https://blog.sentry.io/2016/05/27/php-breadcrumbs.html)
- Raygun - The current PHP SDK doesn't support breadcrumbs
- [Bugsnag - Logging breadcrumbs](https://docs.bugsnag.com/platforms/php/other/#logging-breadcrumbs)

### Tracking releases

Often, it's useful to know which release introduced a particular bug, or which release triggered a regression. Tagging events with a particular release or version number is very easy:

```PHP
$slashtrace->setRelease("1.0.0"); // <- Your version number, commit hash, etc.
```

Tracker docs:

- [Sentry - Releases](https://docs.sentry.io/learn/releases/?platform=javascript)
- [Raygun - Version numbers](https://raygun.com/docs/languages/php#php-version-number)
- Bugsnag - The release version cannot be explicitly set per event. Read the [Bugsnag docs](https://docs.bugsnag.com/platforms/php/other/#tracking-releases) for more details.




