# Phosphor

### Description

Phosphor is a simple and free MVC framework written in pure PHP. In few words: simple but powerful!

### Requirements

* PHP 8.2
* Composer

### Installation

* Require this package: `composer require blaj/phosphor`
* Add to your `index.php`:

```
(new Blaj/Phosphor/Phosphor())
    ->boot([]) // <- place your controller classes
    ->run();
```