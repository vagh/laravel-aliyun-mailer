<?php


namespace Vagh\LaravelAliyunMailer;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->app['swift.transport']->extend('ali_mail', function () {
            $config = $this->app['config']->get('services.ali_mail', []);

            return new MailSender($config);
        });
    }
}