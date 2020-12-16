<?php
 
$sk = new Sock("localhost", 8080);
 
$sk->run();
 
class Sock
{
	public $master;
	
	public $sockets;  //存放所有的socket 包括 $master
	
	public function __construct($address, $port)
	{
		//create a socket
		$this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
		
		//bind socket with address and port
		socket_bind($this->master, $address, $port);
		
		//listen!
		socket_listen($this->master);
		
		$this->sockets = array($this->master); //put master into sockets
		
		echo ('Server Started : '.date('Y-m-d H:i:s') ."\n");
		echo ('Listening on   : '.$address.' port '.$port ."\n");
	}
	
	function run()
	{
		while (true)
		{
			$changes=$this->sockets; //准备监听所有sockets的变化
			
			$write = NULL;
			$except = NULL;
			
			socket_select($changes, $write, $except, NULL);
			
			foreach ($changes as $sock)
			{
				if ($sock == $this->master)
				//如果$master发生变化，代表有新的socket连接
				{
					$client = socket_accept($sock);
					
					$this->add_user($client);
					$this->handshake($client);
					
				}
				else{
					
					$msg = $this->get_message($sock);
 
					if ($msg == '')
						continue;
				
					parse_str($msg, $ar);
					
					$str = $this->code(json_encode($ar));
					
					foreach ($this->sockets as $client)
					{
						//向除了服务器本身之外的所有套接字发送消息
						if ($client == $this->master)
							continue;
						
						socket_write($client, $str, strlen($str));
					}
					
				}
			}
		}
	}
	
	function add_user($client)
	{
		$this->sockets[] = $client;
	}
	
	function get_message($client)
	{
			$buf = '';
			$len = 0;
			$buffer = '';
			
			do{
				$l = socket_recv($client, $buf, 1000, 0);
				$len += $l;
				$buffer .= $buf;
			}while($l == 1000);
			
			return $this->decode($buffer);
			
	}
	
	function handshake($client)
	{
		$len = 0;
		$buffer = '';
		do{
			$l = socket_recv($client, $buf, 1000, 0);
			$len += $l;
			$buffer .= $buf;
		}
		while($l == 1000);
		
		//handshake
		$buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
		
		$key  = trim(substr($buf,0,strpos($buf,"\r\n")));
 
		$new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
 
		$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
		$new_message .= "Upgrade: websocket\r\n";
		$new_message .= "Sec-WebSocket-Version: 13\r\n";
		$new_message .= "Connection: Upgrade\r\n";
		$new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
 
		socket_write($client, $new_message, strlen($new_message));
		
		return true;
		//handshake over
	}
	
	function decode($str){
		$mask = array();
		$data = '';
		$msg = unpack('H*', $str);
		$head = substr($msg[1], 0, 2);
 
		if ($head == '81') 
		{
			$len = substr($msg[1], 2, 2);
			$len = hexdec($len);
 
			if(substr($msg[1], 2, 2) == 'fe')
			{
				$len = substr($msg[1], 4, 4);
				$len = hexdec($len);
				$msg[1] = substr($msg[1],4);
			}
			else if(substr($msg[1], 2, 2) == 'ff'){
				$len = substr($msg[1], 4, 16);
				$len = hexdec($len);
				$msg[1] = substr($msg[1], 16);
			}
 
			$mask[] = hexdec(substr($msg[1], 4, 2));
			$mask[] = hexdec(substr($msg[1], 6, 2));
			$mask[] = hexdec(substr($msg[1], 8, 2));
			$mask[] = hexdec(substr($msg[1], 10, 2));
			$s = 12;
			$n = 0;
		}
		else
		{
			return '';
		}
 
		$e = strlen($msg[1]) - 2;
		
		for ($i = $s; $i <= $e; $i += 2) 
		{
			$data .= chr($mask[$n%4]^hexdec(substr($msg[1], $i, 2)));
			$n ++;
		}
		
		return $data;
	}
 
	function code($msg){
		$frame = array();
		$frame[0] = '81';
		$len = strlen($msg);
		if($len < 126){
			$frame[1] = $len<16?'0'.dechex($len):dechex($len);
		}else if($len < 65025){
			$s=dechex($len);
			$frame[1]='7e'.str_repeat('0',4-strlen($s)).$s;
		}else{
			$s=dechex($len);
			$frame[1]='7f'.str_repeat('0',16-strlen($s)).$s;
		}
		$frame[2] = $this->ord_hex($msg);
		$data = implode('',$frame);
		return pack("H*", $data);
	}
 
	function ord_hex($data)  {
		$msg = '';
		$l = strlen($data);
		for ($i= 0; $i<$l; $i++) {
			$msg .= dechex(ord($data{$i}));
		}
		return $msg;
	}
}
 
?>