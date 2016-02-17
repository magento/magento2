<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Search_Lucene_FSMAction */
#require_once 'Zend/Search/Lucene/FSMAction.php';

/**
 * Abstract Finite State Machine
 *
 * Take a look on Wikipedia state machine description: http://en.wikipedia.org/wiki/Finite_state_machine
 *
 * Any type of Transducers (Moore machine or Mealy machine) also may be implemented by using this abstract FSM.
 * process() methods invokes a specified actions which may construct FSM output.
 * Actions may be also used to signal, that we have reached Accept State
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_FSM
{
    /**
     * Machine States alphabet
     *
     * @var array
     */
    private $_states = array();

    /**
     * Current state
     *
     * @var integer|string
     */
    private $_currentState = null;

    /**
     * Input alphabet
     *
     * @var array
     */
    private $_inputAphabet = array();

    /**
     * State transition table
     *
     * [sourceState][input] => targetState
     *
     * @var array
     */
    private $_rules = array();

    /**
     * List of entry actions
     * Each action executes when entering the state
     *
     * [state] => action
     *
     * @var array
     */
    private $_entryActions =  array();

    /**
     * List of exit actions
     * Each action executes when exiting the state
     *
     * [state] => action
     *
     * @var array
     */
    private $_exitActions =  array();

    /**
     * List of input actions
     * Each action executes when entering the state
     *
     * [state][input] => action
     *
     * @var array
     */
    private $_inputActions =  array();

    /**
     * List of input actions
     * Each action executes when entering the state
     *
     * [state1][state2] => action
     *
     * @var array
     */
    private $_transitionActions =  array();

    /**
     * Finite State machine constructor
     *
     * $states is an array of integers or strings with a list of possible machine states
     * constructor treats fist list element as a sturt state (assignes it to $_current state).
     * It may be reassigned by setState() call.
     * States list may be empty and can be extended later by addState() or addStates() calls.
     *
     * $inputAphabet is the same as $states, but represents input alphabet
     * it also may be extended later by addInputSymbols() or addInputSymbol() calls.
     *
     * $rules parameter describes FSM transitions and has a structure:
     * array( array(sourseState, input, targetState[, inputAction]),
     *        array(sourseState, input, targetState[, inputAction]),
     *        array(sourseState, input, targetState[, inputAction]),
     *        ...
     *      )
     * Rules also can be added later by addRules() and addRule() calls.
     *
     * FSM actions are very flexible and may be defined by addEntryAction(), addExitAction(),
     * addInputAction() and addTransitionAction() calls.
     *
     * @param array $states
     * @param array $inputAphabet
     * @param array $rules
     */
    public function __construct($states = array(), $inputAphabet = array(), $rules = array())
    {
        $this->addStates($states);
        $this->addInputSymbols($inputAphabet);
        $this->addRules($rules);
    }

    /**
     * Add states to the state machine
     *
     * @param array $states
     */
    public function addStates($states)
    {
        foreach ($states as $state) {
            $this->addState($state);
        }
    }

    /**
     * Add state to the state machine
     *
     * @param integer|string $state
     */
    public function addState($state)
    {
        $this->_states[$state] = $state;

        if ($this->_currentState === null) {
            $this->_currentState = $state;
        }
    }

    /**
     * Set FSM state.
     * No any action is invoked
     *
     * @param integer|string $state
     * @throws Zend_Search_Exception
     */
    public function setState($state)
    {
        if (!isset($this->_states[$state])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('State \'' . $state . '\' is not on of the possible FSM states.');
        }

        $this->_currentState = $state;
    }

    /**
     * Get FSM state.
     *
     * @return integer|string $state|null
     */
    public function getState()
    {
        return $this->_currentState;
    }

    /**
     * Add symbols to the input alphabet
     *
     * @param array $inputAphabet
     */
    public function addInputSymbols($inputAphabet)
    {
        foreach ($inputAphabet as $inputSymbol) {
            $this->addInputSymbol($inputSymbol);
        }
    }

    /**
     * Add symbol to the input alphabet
     *
     * @param integer|string $inputSymbol
     */
    public function addInputSymbol($inputSymbol)
    {
        $this->_inputAphabet[$inputSymbol] = $inputSymbol;
    }


    /**
     * Add transition rules
     *
     * array structure:
     * array( array(sourseState, input, targetState[, inputAction]),
     *        array(sourseState, input, targetState[, inputAction]),
     *        array(sourseState, input, targetState[, inputAction]),
     *        ...
     *      )
     *
     * @param array $rules
     */
    public function addRules($rules)
    {
        foreach ($rules as $rule) {
            $this->addrule($rule[0], $rule[1], $rule[2], isset($rule[3])?$rule[3]:null);
        }
    }

    /**
     * Add symbol to the input alphabet
     *
     * @param integer|string $sourceState
     * @param integer|string $input
     * @param integer|string $targetState
     * @param Zend_Search_Lucene_FSMAction|null $inputAction
     * @throws Zend_Search_Exception
     */
    public function addRule($sourceState, $input, $targetState, $inputAction = null)
    {
        if (!isset($this->_states[$sourceState])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined source state (' . $sourceState . ').');
        }
        if (!isset($this->_states[$targetState])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined target state (' . $targetState . ').');
        }
        if (!isset($this->_inputAphabet[$input])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined input symbol (' . $input . ').');
        }

        if (!isset($this->_rules[$sourceState])) {
            $this->_rules[$sourceState] = array();
        }
        if (isset($this->_rules[$sourceState][$input])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Rule for {state,input} pair (' . $sourceState . ', '. $input . ') is already defined.');
        }

        $this->_rules[$sourceState][$input] = $targetState;


        if ($inputAction !== null) {
            $this->addInputAction($sourceState, $input, $inputAction);
        }
    }


    /**
     * Add state entry action.
     * Several entry actions are allowed.
     * Action execution order is defined by addEntryAction() calls
     *
     * @param integer|string $state
     * @param Zend_Search_Lucene_FSMAction $action
     */
    public function addEntryAction($state, Zend_Search_Lucene_FSMAction $action)
    {
        if (!isset($this->_states[$state])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined state (' . $state. ').');
        }

        if (!isset($this->_entryActions[$state])) {
            $this->_entryActions[$state] = array();
        }

        $this->_entryActions[$state][] = $action;
    }

    /**
     * Add state exit action.
     * Several exit actions are allowed.
     * Action execution order is defined by addEntryAction() calls
     *
     * @param integer|string $state
     * @param Zend_Search_Lucene_FSMAction $action
     */
    public function addExitAction($state, Zend_Search_Lucene_FSMAction $action)
    {
        if (!isset($this->_states[$state])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined state (' . $state. ').');
        }

        if (!isset($this->_exitActions[$state])) {
            $this->_exitActions[$state] = array();
        }

        $this->_exitActions[$state][] = $action;
    }

    /**
     * Add input action (defined by {state, input} pair).
     * Several input actions are allowed.
     * Action execution order is defined by addInputAction() calls
     *
     * @param integer|string $state
     * @param integer|string $input
     * @param Zend_Search_Lucene_FSMAction $action
     */
    public function addInputAction($state, $inputSymbol, Zend_Search_Lucene_FSMAction $action)
    {
        if (!isset($this->_states[$state])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined state (' . $state. ').');
        }
        if (!isset($this->_inputAphabet[$inputSymbol])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined input symbol (' . $inputSymbol. ').');
        }

        if (!isset($this->_inputActions[$state])) {
            $this->_inputActions[$state] = array();
        }
        if (!isset($this->_inputActions[$state][$inputSymbol])) {
            $this->_inputActions[$state][$inputSymbol] = array();
        }

        $this->_inputActions[$state][$inputSymbol][] = $action;
    }

    /**
     * Add transition action (defined by {state, input} pair).
     * Several transition actions are allowed.
     * Action execution order is defined by addTransitionAction() calls
     *
     * @param integer|string $sourceState
     * @param integer|string $targetState
     * @param Zend_Search_Lucene_FSMAction $action
     */
    public function addTransitionAction($sourceState, $targetState, Zend_Search_Lucene_FSMAction $action)
    {
        if (!isset($this->_states[$sourceState])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined source state (' . $sourceState. ').');
        }
        if (!isset($this->_states[$targetState])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined source state (' . $targetState. ').');
        }

        if (!isset($this->_transitionActions[$sourceState])) {
            $this->_transitionActions[$sourceState] = array();
        }
        if (!isset($this->_transitionActions[$sourceState][$targetState])) {
            $this->_transitionActions[$sourceState][$targetState] = array();
        }

        $this->_transitionActions[$sourceState][$targetState][] = $action;
    }


    /**
     * Process an input
     *
     * @param mixed $input
     * @throws Zend_Search_Exception
     */
    public function process($input)
    {
        if (!isset($this->_rules[$this->_currentState])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('There is no any rule for current state (' . $this->_currentState . ').');
        }
        if (!isset($this->_rules[$this->_currentState][$input])) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('There is no any rule for {current state, input} pair (' . $this->_currentState . ', ' . $input . ').');
        }

        $sourceState = $this->_currentState;
        $targetState = $this->_rules[$this->_currentState][$input];

        if ($sourceState != $targetState  &&  isset($this->_exitActions[$sourceState])) {
            foreach ($this->_exitActions[$sourceState] as $action) {
                $action->doAction();
            }
        }
        if (isset($this->_inputActions[$sourceState]) &&
            isset($this->_inputActions[$sourceState][$input])) {
            foreach ($this->_inputActions[$sourceState][$input] as $action) {
                $action->doAction();
            }
        }


        $this->_currentState = $targetState;

        if (isset($this->_transitionActions[$sourceState]) &&
            isset($this->_transitionActions[$sourceState][$targetState])) {
            foreach ($this->_transitionActions[$sourceState][$targetState] as $action) {
                $action->doAction();
            }
        }
        if ($sourceState != $targetState  &&  isset($this->_entryActions[$targetState])) {
            foreach ($this->_entryActions[$targetState] as $action) {
                $action->doAction();
            }
        }
    }

    public function reset()
    {
        if (count($this->_states) == 0) {
            #require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('There is no any state defined for FSM.');
        }

        $this->_currentState = $this->_states[0];
    }
}

