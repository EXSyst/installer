# EXSyst Installer

This composer plugin allows to quickly register symfony bundles into your app.

## How to install it?

Execute the following command in a terminal:
```console
$ composer global require exsyst/installer 0.0.x-dev
```
And that's all, the plugin is working!

## How to use it?

Each time you will require a symfony bundle, the plugin will detect it and ask if you want to configure it:
```console
~/my-symfony-app $ composer require friendsofsymfony/rest-bundle
Configure "friendsofsymfony/rest-bundle"? [no]: y
Add the bundle "FOS\RestBundle\FOSRestBundle" to your kernel "AppKernel"? [yes]: 
FOS\RestBundle\FOSRestBundle has been registered in AppKernel.
```

## What does it support?

For now, the plugin is only able to register bundles in your kernel.
But in the future, I would like it to be able to configure the most important features of bundles, generate classes, etc.

## Contributing

If you want to change something or have an idea, submit an issue or open a pull request :)
