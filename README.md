Siphon
======

A MySQL to Redis data-push Task for FuelPHP

Siphon pushes MySQL data to Redis in the form of hashes to represent models and sets to represent has_many and many_many relationships.

Download Siphon
---------------

**Clone on GitHub**

GitHub is the easiest way to install the Siphon package.

    $ git clone git://github.com/mcrumm/siphon.git fuel/app/vendor/.

**Get Siphon via Composer**

You can also install siphon via Composer, if that’s your sort of thing (and it should be.)

*Install Composer*

    $ curl -s https://getcomposer.org/installer | php

Then add the following to `fuel/app/composer.json`

    {
        "require": {
            "mcrumm/siphon": "master-dev"
        }
    }

Using Siphon
------------

Once you’ve downloaded the Siphon code, either from GitHub or via Composer, copy `fuel/app/vendor/mcrumm/siphon/config/siphon.php` to `fuel/app/config/siphon.php`

Modify the config settings to match your environment.  You’ll need to specify the models that you’d like to push to redis.  Siphon won’t assume to push any data until you tell it to, so if you don’t specify any models, you’ll get an error when you run the task.

**Config Options**

key_prefix:  A prefix used when generating keys for redis.  (Default: ‘siphon’)
key_separator:  The separator between key parts.  (Default: ‘:’)
models:  Holds references to the models you want to push.  The format here is important.

**Format for ‘models’ config option:**

The ‘models’ config option should be an array, whose keys are the model names and whose values are arrays containing (optional) config parameters for the individual model specified.   An example is included that matches model generated for Users when using the SimpleAuth authentication mechanism built into Fuel.

**Example Config:**

    return array(
        'key_prefix' => 'siphon',        
        'key_separator' => ':',
        'models' => array(
            'user' => array(
                'exclude' => array(
                    'password',
                    'login_hash',
                    'profile_fields'
                ),
            ),
        ),
    );

To load the Siphon package, modify the `always_load` packages in `fuel/app/config.php`:

    'packages' => array(
      array('siphon' => APPPATH.'vendor'.DS.'mcrumm'.DS.'siphon'.DS),
    ),

Running Siphon is as easy as:

    $ php oil refine siphon

At the end of its run, Siphon will output each redis command executed, so you can save the output and run it again without needing to hit the database.

Here’s an example of saving the siphon run:

    $ php oil refine siphon > /path/to/siphon.out