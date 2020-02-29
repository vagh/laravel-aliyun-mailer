<?php


namespace Vagh\LaravelAliyunMailer;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Swift_Mime_SimpleMessage;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Arr;

class MailSender extends Transport
{
    protected $access_key;
    protected $access_key_secret;
    protected $region_id;
    protected $options;

    /**
     * MailSender constructor.
     * @param $options
     * @throws ClientException
     */
    public function __construct($options)
    {
        $this->options = $options;
        $this->region_id = Arr::get($options, 'region_id', 'cn-hangzhou');
        $access_key = Arr::get($options, 'access_key');
        $access_key_secret = Arr::get($options, 'access_key_secret');

        AlibabaCloud::accessKeyClient($access_key, $access_key_secret)
            ->regionId($this->region_id)
            ->asDefaultClient();
    }

    /**
     * 发送一封邮件
     * @param Swift_Mime_SimpleMessage $message
     * @param null $failedRecipients
     * @return int
     * @throws ClientException
     * @throws ServerException
     * @author yuzhihao <yu@vagh.cn>
     * @since 2020/2/29
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        $message->setBcc([]);

        AlibabaCloud::rpc()
            ->product('Dm')
            ->scheme('https')
            ->version('2015-11-23')
            ->action('SingleSendMail')
            ->method('POST')
            ->host('dm.aliyuncs.com')
            ->options([
                'query' => $this->genAliyunParams($message),
            ])
            ->request();

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * 拼接阿里云SDK所需参数
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     * @author yuzhihao <yu@vagh.cn>
     * @since 2020/2/29
     */
    private function genAliyunParams(Swift_Mime_SimpleMessage $message)
    {
        return [
            // 管理控制台中配置的发信地址
            'AccountName' => Arr::get($this->options, 'from_address', config('mail.from.address', key($message->getFrom()))),
            // 使用管理控制台中配置的回信地址
            'ReplyToAddress' => 'true',
            // 地址类型 [0：为随机账号|1：为发信地址]
            'AddressType' => Arr::get($this->options, 'address_type', 1),
            // 目标地址，多个 email 地址可以用逗号分隔，最多100个地址
            'ToAddress' => $this->getTo($message),
            // 发信人昵称，长度小于15个字符
            'FromAlias' => Arr::get($this->options, 'from_alias'),
            //邮件主题，建议填写。
            'Subject' => $message->getSubject(),
            // 邮件 html 正文，限制28K。
            'HtmlBody' => $message->getBody(),
            // 数据跟踪功能[1：打开|2：关闭]
            'ClickTrace' => Arr::get($this->options, 'click_trace', 0),
        ];
    }

    /**
     * 获取收件人
     * @param Swift_Mime_SimpleMessage $message
     * @return string
     * @author yuzhihao <yu@vagh.cn>
     * @since 2020/2/29
     */
    protected function getTo(Swift_Mime_SimpleMessage $message)
    {
        return collect($this->allContacts($message))->map(function ($display, $address) {
            return $display ? $display . " <{$address}>" : $address;
        })->values()->implode(',');
    }

    /**
     * 拼接对应数组
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     * @author yuzhihao <yu@vagh.cn>
     * @since 2020/2/29
     */
    protected function allContacts(Swift_Mime_SimpleMessage $message)
    {
        return array_merge(
            (array)$message->getTo(), (array)$message->getCc(), (array)$message->getBcc()
        );
    }
}