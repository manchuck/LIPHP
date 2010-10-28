<?php
/**
 * PHP version 5.2
 *
 * @category   Demo
 * @package    LIPHP
 * @subpackage Unit Test
 * @author     Chuck Reeves <chuck.reeves@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL v2 Licence
 * @version    $Id: $
 * @link       https://code.google.com/p/liphp-manchuck-unit/source/browse/#svn/trunk
 */
class Account
{
    const FEE_ATM       = 3.00;
    const FEE_OVERDRAFT = 35.00;

    const TRANSACTION_CHECK   = 1;
    const TRANSACTION_ATM     = 2;
    const TRANSACTION_TELLER  = 3;

    const TRANSACTION_NAME_DEPOSIT  = 'Deposit';
    const TRANSACTION_NAME_WIDTHDRAWL  = 'Widthdrawl';

    private $_balance   = 0.00;

    private $_id;

    private $_name;

    private $_transactions  = array();

    protected static $db;

    public function __construct($id = null, $pin = null)
    {
        if (null !== $id)
        {
            $this->setAccount($id, $pin);
        }
    }

    public function setAccount($id, $pin)
    {
        $db     = self::getDb();
        $stmt   = $db->prepare('SELECT id, name FROM account WHERE id = :id AND pin = :pin');
        $stmt->execute(array('id'   => (int) $id,
                            'pin'   => (int) $pin));

        $res    = $stmt->fetch();
        if (false === $res)
        {
            throw new InvalidArgumentException('Invalid Account ID or Pin');
        }


        $this->_name    = $res['name'];
        $this->_id      = (int) $res['id'];
        $this->_loadTransactions();
    }

    final function _loadTransactions()
    {
        $db     = self::getDb();
        $stmt   = $db->prepare('SELECT account, amount, name, type FROM account_transactions WHERE account = :id');
        $stmt->execute(array('id'   => (int) $this->_id));


        while ($res = $stmt->fetch())
        {
            if (false === $res)
            {
                throw new InvalidArgumentException('Invalid Account ID');
            }

            $this->_popTransaction($res);
        }
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getTransactions()
    {
        return $this->_transactions;
    }

    public function getBalance()
    {
        return $this->_balance;
    }

    public function deposit($amount, $type)
    {
        if (null === $this->_id )
        {
            throw new InvalidArgumentException('Account is not loaded');
        }

        //ensure positive
        $amount = abs($amount);
        $this->processTransaction('Deposit', $amount, $type);
    }

    public function withdraw($amount, $type)
    {
        if (null === $this->_id)
        {
            throw new InvalidArgumentException('Account is not loaded');
        }

        //ensure negative
        $amount = abs($amount) * -1;
        $this->processTransaction('Widthdrawl', $amount, $type);
    }

    protected function processTransaction($name, $amount, $type)
    {
        $amount = (float) $amount;

        if ($type == self::TRANSACTION_ATM)
        {
            $this->_addTransaction('ATM Fee', self::FEE_ATM * -1, $type);
        }

        if (($amount * -1) > $this->_balance)
        {
            $this->_addTransaction('Overdraft Fee', self::FEE_OVERDRAFT * -1, $type);
        }

        $this->_addTransaction($name, $amount, $type);
    }

    private function _addTransaction($name, $amount, $type)
    {
        $transData  = array('account'   => $this->_id,
                            'amount'    => (float) $amount,
                            'name'      => $name,
                            'type'      => (int) $type);

        $db     = self::getDb();
        $stmt   = $db->prepare('INSERT INTO account_transactions (account, amount, name, type) VALUES (:account, :amount, :name, :type)');

        $stmt->execute($transData);
        $this->_popTransaction($transData);
    }

    private function _popTransaction(array $transData)
    {
        $this->_transactions[]  = array('account'   => (int) $transData['account'],
                                        'amount'    => (float) $transData['amount'],
                                        'name'      => $transData['name'],
                                        'type'      => (int) $transData['type']);

        $amount = round($transData['amount'], 2);
        $this->_balance += $amount;
    }


    /**
     * @return PDO
     */
    protected static function getDb()
    {
        if (null == self::$db)
        {
            self::$db = new PDO('mysql:host=localhost;dbname=liphp_unit', 'unitUser', '12345');
        }

        return self::$db;
    }
}