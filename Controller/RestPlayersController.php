<?php
App::uses('AppController', 'Controller');

/**
 * Players Controller
 */
class RestPlayersController extends AppController {

/**
 * Scaffold
 *
 * @var mixed
 */
	public $scaffold;
	
	public $components = array('RequestHandler');
	public $uses = array('Player'); // Model
	
	public function beforeFilter() {
		parent::beforeFilter();
		// Allow users to register and logout.
		$this->Auth->allow('add', 'logout');
	}
	
	
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
	public function add() {
	     $this->Player->create();
        if ($this->Player->save($this->request->data)) {
            $message = 'Saved';
			$message = array("statusCode"=>200,"status"=>"Success","statusMessage"=>array("Player Registered!"));
        } else {
			$errors = $this->Player->validationErrors;
			//$this->response->setStatus(201);
            $message = array("statusCode"=>200,"status"=>"Error","statusMessage"=>$errors);
        }
        $this->set(array(
            'message' => $message,
            '_serialize' => array('message')
        ));
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
	
	public function login() {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				return $this->redirect($this->Auth->redirectUrl());
			}
			$this->Flash->error(__('Invalid username or password, try again'));
		}
	}

	public function logout() {
		return $this->redirect($this->Auth->logout());
	}
	
}
