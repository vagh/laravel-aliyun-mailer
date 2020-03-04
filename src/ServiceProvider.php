<?php


namespace Vagh\LaravelAliyunMailer;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
    	$manager_name = $this->getTransportManager();

        $this->app[$manager_name]->extend('ali_mail', function ($app) {
            $config = $this->app['config']->get('services.ali_mail', []);

            return new MailSender($config);
        });
    }

    /**
     * Resolve the mail manager.
     *
     * @return \Illuminate\Mail\TransportManager|\Illuminate\Mail\MailManager
     */
    public function getTransportManager()
    {
        if ($this->app->has('mail.manager')) {
            return 'mail.manager';
        }

        return 'swift.transport';
    }
}