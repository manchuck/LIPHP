<?php
/**
 * PHP version 5.2
 *
 * This is demo code for the PHP Unit Testing.  All test classes must
 * end in Test, contain one test method and extend:
 *
 * PHPUnit_Extensions_Database_TestCase
 * - or -
 * PHPUnit_Framework_TestCase
 *
 * Methods that define the a test must begin with test<some test name>
 * When using logging or testdox, camel cased names will be exploded
 * into seperate words ex. "testCanWithdraw" becomes "Can Withdraw"
 * Before each test, PHP Unit will call the setUp hook.  This allows you
 * to set up resoucres for all tests run.
 *
 * @category   Tests
 * @package    LIPHP
 * @subpackage account
 * @author     Chuck Reeves <chuck.reeves@gmail.com>
 * @copyright  2010 Chuck Reeves
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL v2 Licence
 * @version    $Id: $
 * @link       https://code.google.com/p/liphp-manchuck-unit/source/browse/#svn/trunk
 */
require_once(APPLICATION_PATH . '/Account.php');

class AccountTest
    extends ControllerTestCase
{
    protected $account;

    /**
     * Here you can set up resources that your test might need
     * Ex:
     *
     * - Create a SOAP Object
     * - Make a new file
     * - Open Database connection
     *
     * Be sure to call the parent setUp method.
     */
    public function setUp()
    {
        parent::setUp();
        $this->account  = new Account();
        $this->assertTrue(true);
    }

    /**
     * Tear down is called at the end of each test.
     * This allows you to clean up any resources that you opened
     * during the setUp method
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->account  = null;
        unset($this->account);
        $this->assertTrue(true);
    }

    /**
     * Here we are testing that the start balance is set to Zero
     */
    public function testAccountStartsAtZero()
    {
    	$this->assertEquals(0.00, $this->account->getBalance());
    }

    /**
     * Basic test to check that account information will load
     */
    public function testAccountLoads()
    {
        $this->account->setAccount(1, 1234);
        $this->assertEquals(2, count($this->account->getTransactions()));
        $this->assertEquals('test', $this->account->getName());
    }

    /**
     * Test the correct percision and that floats round down
     */
    public function testDepositPercisionAndRoundDown()
    {
        $this->account->setAccount(1, 1234);
        $this->account->deposit(2.50152, Account::TRANSACTION_TELLER);
        $this->assertEquals($this->account->getBalance(), 852.50);
    }

    /**
     * More tests that follow requirments
     */
    public function testWithdrawTellerNoFeeAndRoundDown()
    {
        $this->account->setAccount(1, 1234);
        $this->account->withdraw(2.50677, Account::TRANSACTION_TELLER);
        $this->assertEquals(847.49, $this->account->getBalance());
    }

    /**
     * Test that deposits create transactions
     */
    public function testMultipleDepositsAddTransactions()
    {
        $this->account->setAccount(1, 1234);
        $this->assertEquals(2, count($this->account->getTransactions()));

        $this->account->deposit(2.50, Account::TRANSACTION_TELLER);
        $this->account->deposit(2.50, Account::TRANSACTION_TELLER);
        $this->assertEquals($this->account->getBalance(), 855.00);

        $this->assertEquals(4, count($this->account->getTransactions()));
    }

    /**
     * Test that widthdrawal creates multiple transactions
     */
    public function testMultipleWidthdrawlsAddTransactions()
    {
        $this->account->setAccount(1, 1234);
        $this->assertEquals(2, count($this->account->getTransactions()));

        $this->account->withdraw(2.50, Account::TRANSACTION_TELLER);
        $this->account->withdraw(2.50, Account::TRANSACTION_TELLER);
        $this->assertEquals($this->account->getBalance(), 845.00);

        $this->assertEquals(4, count($this->account->getTransactions()));
    }

    /**
     * Test that ATM adds fee transaction
     */
    public function testAtmWithdrawalFee()
    {
        $this->account->setAccount(1, 1234);

        $this->assertEquals(3.00, Account::FEE_ATM);
        $this->assertEquals(850.00, $this->account->getBalance());
        $this->assertEquals(2, count($this->account->getTransactions()));

        $this->account->withdraw(40.00, Account::TRANSACTION_ATM);
        $this->assertEquals(807.00, $this->account->getBalance());
        $this->assertEquals(4, count($this->account->getTransactions()));
    }

    /**
     * Test that overdraft fee is applied
     */
    public function testOverdraftFeeCharged()
    {
        $this->account->setAccount(1, 1234);

        $this->assertEquals(35.00, Account::FEE_OVERDRAFT);
        $this->assertEquals(850.00, $this->account->getBalance());
        $this->assertEquals(2, count($this->account->getTransactions()));

        $this->account->withdraw(900.00, Account::TRANSACTION_CHECK);
        $this->assertEquals(-85.00, $this->account->getBalance());
        $this->assertEquals(4, count($this->account->getTransactions()));
    }

    /**
     * You can test that an exception is thrown by useing the
     * expected exception tag
     *
     * @expectedException InvalidArgumentException
     */
    public function testCantDepositEmptyAccount()
    {
        $this->account->deposit(2.50, Account::TRANSACTION_TELLER);
    }

    /**
     * Bug 8456 ATM fee is not charging overdraft
     */
    public function testBug8546_AtmFeeChargesOverDraft()
    {
        $this->account->setAccount(1, 1234);
        $this->assertEquals(2, count($this->account->getTransactions()));
        $this->account->withdraw(850.00, Account::TRANSACTION_ATM);

        $this->assertEquals(-38.00, $this->account->getBalance());
        $this->assertEquals(5, count($this->account->getTransactions()));
    }

    /**
     * Bug 675 happens when a comma is in the number
     */
    public function testBug675_CommaInDepositFix()
    {
        $this->account->setAccount(1, 1234);

		$this->account->deposit("1,234.56", Account::TRANSACTION_TELLER);
		$this->assertEquals(1873.56, $this->account->getBalance());
    }

    /**
     * Bug 9083 happens now that we are supporting foregin formats
     */
    public function testBug9083_FormatItalianDepositFix()
    {
        $this->account->setAccount(1, 1234);

        $this->account->deposit("10.234,56", Account::TRANSACTION_TELLER);
        $this->assertEquals(10873.56, $this->account->getBalance());
    }
}