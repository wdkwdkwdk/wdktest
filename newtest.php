<?php
//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg(); 
class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr))
        {
                
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
                $image = $postObj->MsgType;
                $pic = $postObj->PicUrl;
            $keyword = trim($postObj->Content);
            $time = time();
                
            $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <FuncFlag>0<FuncFlag>
            </xml>";
                
                if($MsgType=="event")
                {
                    if($event=="subscribe")
                        {
                            
                            $msgType = "text";
                            $contentStr = "给用户推送的第一条内容";
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                            echo $resultStr;
                        }
                }
                
                if(!empty( $keyword ))  
                {
                        if(eregi("价格", $keyword))
                        {
                            $keyword = str_ireplace("价格","",$keyword);
                            //调用接口
                            $url = 'http://1.superwdk.sinaapp.com/data.php?c=price';
                            $f = new SaeFetchurl();
                            $content = $f->fetch($url);
                            $price = json_decode($content,true);
                            //调用结束
                            $url = $price['url'];
                            $localprice = $price['localprice'];
                            $wholesale = $price['wholesale'];
                            $market = $price['market'];
                            $picurl = $price['priceimg'];
                            $word = $keyword."的生产地价格为:".$localprice."\n批发市场价格为:".$wholesale."\n超市价格为:".$market."\n点击查看详情";
                            $textTpl = "
                                <xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[news]]></MsgType>
                                <ArticleCount>1</ArticleCount>
                                <Articles>
                                <item>
                                <Title><![CDATA[".$keyword."的价格]]></Title>
                                <Description><![CDATA[".$word."]]></Description>
                                <PicUrl><![CDATA[$picurl]]></PicUrl>
                                <Url><![CDATA[$url]]></Url>
                                </item>
                                </Articles>
                                <FuncFlag>0</FuncFlag>
                                </xml> ";
                            $msgType = "news";
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "图片",$url,$url);
                            echo $resultStr;      
                        }

                        if(eregi("供应", $keyword))
                        {
                            $keyword = str_ireplace("供应","",$keyword);
                            //调用接口
                            $url = 'http://1.superwdk.sinaapp.com/data.php?c=sell';
                            $f = new SaeFetchurl();
                            $content = $f->fetch($url);
                            $sell = json_decode($content,true);
                            //调用结束
                            $number = $sell['number'];
                            $in = '';
                            for ($i=1;$i<=$number;$i++) 
                            {   
                                $imgurl = $sell["a".$i]['imgurl'];
                                $title = $sell["a".$i]['title'];
                                $price = $sell["a".$i]['price'];
                                $url = $sell["a".$i]['url'];
                                $in = $in."<item>
                                            <Title><![CDATA[".$title."|".$price."]]></Title>
                                            <Description><![CDATA[".$price."]]></Description>
                                            <PicUrl><![CDATA[".$imgurl."]]></PicUrl>
                                            <Url><![CDATA[".$url."]]></Url>
                                            </item>";

                            }
                            $textTpl = "
                            <xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[news]]></MsgType>
                            <ArticleCount>".$number."</ArticleCount>
                            <Articles>
                            ".$in."
                            </Articles>
                            <FuncFlag>0</FuncFlag>
                            </xml> ";
                            $msgType = "news";
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "图片",$url,$url);
                            echo $resultStr;



                        }
                        
                        $msgType = "text";
                        $contentStr = "点击按钮使用功能~";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                        
                        
                }
            }
            else 
            {
                echo "";
                exit;
            }
    }
        
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}

?>