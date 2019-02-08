# PHPUnit Testing Components

## Getting started running tests locally

You can either install and run the tests from your OS terminal, or at a virtual machine level (if you are using Local By Flywheel for example). Either way you need to open a terminal into that environment (`Open Site SSH` for Local by Flywheel).

After that, you need to make sure that you have `wget`, `git`, `svn`, `php` at the local machine level. For example here are the commands you need to run when SSH-ing into a Local By Flywheel machine:
1. Install _wget_
    `apt-get update`
    `apt-get install wget`
2. Install _svn_ (this is needed to download the WP test library)
    `apt-get install subversion`
3. Install git
    `apt-get install git`
    
I am assuming you already have `php` installed, since you are running WordPress :)

After this setup, you need to go with the terminal to the `components` folder, and install the WP test library:
`.bin/install-wp-tests.sh wordpress_test root root localhost`
Again, I am assuming you are in a Local By Flywheel machine. If you are not, simply use your own user and password above. I recommend you use `wordpress_test` as the database name as it will be created and overwritten.

Please make sure that you have installed the tests' PHP dependencies by running `composer install` in the `components/tests/php` directory. If you don't have Composer installed, check [this](https://getcomposer.org/) out.

After the WP test library was successfully installed, you can simply run, from the `components` directory (we are using a local version of PHPUnit installed with Composer):
`./tests/php/vendor/bin/phpunit`

Now the tests should run and you should receive a report with what has happened.
