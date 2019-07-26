# Installation
Welcome to this installation tutorial and thank you for choosing OpenVK 5!

While we take care about creating installation script, you can do installation by yourself, following these simple steps.

## Checking your environment
Please make sure that your environment meets following system requirements:

| _      | Requirement          |
|--------|----------------------|
|OS |x64 Linux / Windows Server*|
|Server  | PHP-DS or Apache2.4+ |
|PHP     | 7.2.8 or higher      |
|Composer| Yes                  |
|SQLite  | 3.27+                |
|PDO     | Yes                  |
|mbstring| Yes                  |
|iconv   | Yes                  |
|sodium  | Yes                  |
|Imagick | Yes                  |

(* -> experimental).

Notice: this system requirements are subject to change.

## Getting started
### Shell access required
Steps below require shell access and ownership of document root. If you don't have shell access, don't worry, symlinks and Git aren't dangerous, so your hosting provider will probably do this for you upon your request. Contact your support and give them link to this instruction.

First, clone the repository somewhere (outside your document root):
```sh
cd ~
git clone https://github.com/l-lsoc/openvk5.git
cd openvk
```
Then, delete your document root and create a symlink to public folder:
```sh
ln -s public /var/www/example.org
```

Congratulations! Now you have downloaded source code to your server!

## Setting up database
Currently, OpenVK supports only SQLite3. SQLite database is already bundled with source code, so, if you are not in hacking mood, feel free to skip this step.

You may want to try converting schema to your RDBMS. [Schema](https://hastebin.com/cojopaniho.sql).

## Configuration
You may now edit `SocialConfig-examole.php` file, which is located in root of source code.

### Site parameters
Change ```$xSiteName``` to your desired name and ```$xSiteURL``` to full URL to your website (this includes protocol, domain name, port and path) without trailing slash.

Leave skin parameter `NULL` for now. 

### Database
Leave as is, if you have skipped database step. Otherwise, configure it. You may find [this](https://www.php.net/manual/ru/pdo.construct.php#refsect1-pdo.construct-parameters) helpful.

### Security
```$xSecret``` is going to be your secret key. It is used for updating your installation, signing sessions and providing security. Generate 64 characters long string and put it there (while it doesn't matter which string it is exactly, but if it will be generated with cryptosecure algorithms, it will provide more security).

To continue, you need to register an account in [HERE](https://developer.here.com/?create=Freemium-Basic&keepState=true&step=account) and in [hCaptcha](https://hcaptcha.com/webmaster/signup).
#### Setting up HERE
After registration, go to your dashboard, select your project and create JS app, then generate the key and copy it to ```$xHereKey```.
#### Setting up hCaptcha
After registration, go to your dashboard, select your project and scroll to the bottom, until you will see drop-in snippets. Copy `data-sitekey` from "HTML form" snippet to ```$xCaptchaSiteKey``` and `params.secret` from "Verify on your server" snippet. 

Set the ```$xCaptchaVerifyIP``` to `false` if you are going to use CloudFlare/Stackpath/Any proxy for serving website. You must also set this to `false` if you are going to serve your website in local network (e.g, for development or for intranet).

### Finishing configuration
To apply this configuration, just remove "-example" from filename:
```shell
mv SocialConfig-example.php SocialConfig.php
```

### Experiments
If you want to have latest and untested, unstable, but cool features, you can set ```$xExperiments``` to `true` and add desired experiments' ids to ```$xAllowedExperiments```.

Currently available experiments:

| Experiment       | ID            | Description                                                          |
|------------------|---------------|----------------------------------------------------------------------|
| Moderation panel | `Admin.ModCP` | Enables content reports and moderation panel, available at `?/modcp` |

## Finishing installation
You may now login to your installation.

Default credentials (if you are using default database): `root:password`. You can edit your info in profile settings.
