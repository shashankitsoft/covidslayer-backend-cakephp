<?php
App::uses('AppController', 'Controller');
/**
 * Players Controller
 */
class PlayersController extends AppController {

/**
 * Scaffold
 *
 * @var mixed
 */
	public $scaffold;
	
	public $components = array('RequestHandler');

    public function index() { 
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
        } else {
			$errors = $this->Player->validationErrors;
			//$this->response->setStatus(201);
            $message = 'Error';
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
}
