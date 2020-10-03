<?php
App::uses('AppController', 'Controller');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

/**
 * Players Controller
 */
class RestAuthController extends AppController {

/**
 * Scaffold
 *
 * @var mixed
 */
	public $scaffold;
	
	public $components = array('RequestHandler');
	public $uses = array('Player'); // Model
	
	
    public function index() { 
		//$this->loadModel('Player');
        $players = $this->Player->find('all'); 
        $this->set(array(
            'players' => $players,
            '_serialize' => array('players')
        ));
    }
	public function view($id) {
        $player = $this->Player->findById($id);
        $this->set(array(
            'player' => $player,
            '_serialize' => array('player')
        ));
    }
	public function register() {
		if ($this->request->is('post')) {
			$this->Player->set($this->request->data);	
			if ($this->Player->validates()) { 
				try {
					$this->Player->create();
					
					if ($this->Player->save($this->request->data)) {
						$message = 'Saved';
						$response = array("statusCode"=>200,"status"=>"Success","success"=>array('Player'=>array('id'=>$this->Player->id)),"message"=>array("Player Registered!"));
					} else {						
						$response = array("statusCode"=>200,"status"=>"Error","success"=>false,"message"=>array("Error in registering."));
					}
				}catch(Exception $e){
					$response = array("statusCode"=>200,"status"=>"Error","success"=>false,"message"=>array("Error in registering.",$e->getMessage()) );
					//echo $e->getMessage(); die;
				}
			}else {
					$errors = $this->Player->validationErrors; // print_r($errors); die;
					$response = array("statusCode"=>200,"status"=>"Error","success"=>false,"message"=>array_values($errors));
			}
			
			$this->set(array(
				'response' => $response,
				'_serialize' => array('response')
			));
		}
    }
	
	public function login() {
		if ($this->request->is('post') || $this->request->is('get')) { 		
		
			$neednewToken = false;
			$playerData = array();
			$reqAuthHeader = $this->request->header('Authorization'); 
			//$this->log(PHP_EOL.$reqAuthHeader, 'debug');

			if(!empty($reqAuthHeader) && (stripos($reqAuthHeader, 'Bearer ')!== false) ){ // Bearer token
				$neednewToken = false; 
				$playerData = $this->Player->find('first', array(
					'fields' => array('id','fullname','email','avatar','status','token'),
					'conditions' => array('Player.token' => str_ireplace('Bearer ','',$reqAuthHeader))
				));
				// $this->log(PHP_EOL.print_r($playerData,true), 'debug');
				// $log = $this->Player->getDataSource()->getLog(false, false); // get raw sql query
				// $this->log(PHP_EOL.print_r($log,true), 'debug');
				
			}elseif(!empty($reqAuthHeader) && (stripos($reqAuthHeader, 'Basic ')!== false) ){ // Basic auth
				$neednewToken = false; 
				$playerData = $this->Player->find('first', array(
					'fields' => array('id','fullname','email','avatar','status','token'),
					'conditions' => array('Player.token' => str_ireplace('Basic ','',$reqAuthHeader))
				));
				$this->log(PHP_EOL."b", 'debug');
			}else{ 
				$reqPlayer =  $this->request->data;  // $this->request->input('json_decode'); // // Basic username/password matching
				//print_r($reqPlayer); die;
				$neednewToken = true;
				$this->log(PHP_EOL."c", 'debug');
				if(!empty($reqPlayer['email']) && !empty($reqPlayer['password'])){
					$playerData = $this->Player->find('first', array(
						'fields' => array('id','fullname','email','avatar','status','token'),
						'conditions' => array('Player.email' => $reqPlayer['email'],'Player.password' => md5($reqPlayer['password'])
						)
					));
					
				}else{			
					$response = array("statusCode"=>200,"status"=>"Error","success"=>false,"message"=>array("Please enter email and password to login!"));
						
				}
			}
			// print_r($playerData); die('dd');
			if(is_countable($playerData) && count($playerData)){ 
				// $this->log(PHP_EOL."Got player data.", 'debug');
				if($neednewToken){ 
					$token = $this->generateToken($playerData['Player']['email'],$reqPlayer['password']);

					// save new token in db	
					$this->Player->updateAll(
						array('token' => "'$token'"),
						array('email' => $playerData['Player']['email'])
					);
					$playerData['Player']['token'] = $token;
				}
				$response = array("statusCode"=>200,"status"=>"Success","success"=>$playerData,"token"=>$playerData['Player']['token'],"message"=>"Logged in Successfully!");								
			}			
			else{ 
				$response = array("statusCode"=>200,"status"=>"Error","success"=>false,"message"=>array("Invalid Credentials!"));			
				
			}
			
			$this->set(array(
				'response' => $response,
				'_serialize' => array('response')
			));
		}
    }
	
	public function generateToken($email,$password){
		//print_r($player); 
		//$token = md5($player['email'].date('Y-m-d H:i:s'));
		$token = base64_encode($email . ':' . $password);  // this will same to Basic Auth token with username/password
		return $token; 
		
	}
	
	public function login_blowfish() {
		if ($this->request->is('post')) {	
			$reqPlayer = $this->request->input('json_decode'); // $this->request->data; print_r($x); die('dd');
			if(empty($reqPlayer->email) || empty($reqPlayer->password)){
				$message = array("statusCode"=>200,"status"=>"Error","statusMessage"=>array("Please enter email and password to login!"));				
			}
			else{
				$playerData = $this->Player->find('first', array(
					'conditions' => array('Player.email' => $reqPlayer->email)
				));
				//print_r($playerData); die('dd');
				if(!count($playerData)){
					$message = array("statusCode"=>200,"status"=>"Error","statusMessage"=>array("Invalid User!"));
					
				}
				else{
					//echo $reqPlayer->password; die('dd');
					$passwordHasher = new BlowfishPasswordHasher();
					//echo $passwordHasher->hash("mypwd"); die('dd');
					
					if(!$passwordHasher->check($reqPlayer->password,$playerData['Player']['password'])){
						$message = array("statusCode"=>200,"status"=>"Error","statusMessage"=>array("Wrong Password!"));
						
					}			
					else{
						$message = array("statusCode"=>200,"status"=>"Success","statusMessage"=>$playerData);
					}
				}
			}
			/*if ($this->Auth->login()) {
				// return $this->redirect($this->Auth->redirectUrl());
				$message = array("statusCode"=>200,"status"=>"Success","statusMessage"=>array("Logged in successfully"));
				
			}else{
				$message = array("statusCode"=>200,"status"=>"Error","statusMessage"=>array("Logging Failed!"));
			}*/
			
			$this->set(array(
				'message' => $message,
				'_serialize' => array('message')
			));
		}
    }
	
	public function access_denied()
	{
		$loggedIn = $this->Auth->user('id');
		$response = [
			'result' => false,
			'code' => 'access-denied',
			'message' => 'Invalid credentials or access denied.'
		];
		$this->set(compact('loggedIn', 'response'));
		$this->set('_serialize', ['loggedIn', 'response']);
	}
	
	public function edit($id) {
        $this->Player->id = $id;
        if ($this->Player->save($this->request->data)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set(array(
            'message' => $message,
            '_serialize' => array('message')
        ));
    }

    public function delete($id) {
        if ($this->Player->delete($id)) {
            $message = 'Deleted';
        } else {
            $message = 'Error';
        }
        $this->set(array(
            'message' => $message,
            '_serialize' => array('message')
        ));
    }
}
