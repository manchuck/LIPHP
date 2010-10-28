<?php
/**
 * Test Controller
 *
 * PHP version 5.2
 *
 * @category   Test
 * @package    LIPHP
 * @subpackage Controllers
 * @author     Chuck Reeves <chuck.reeves@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL v2 Licence
 * @version    $Id: $
 * @link       https://code.google.com/p/liphp-manchuck-unit/source/browse/#svn/trunk
 */

require 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';
require 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * By extending creating a controller class, we can set up global
 * fixtures for each class and set up help methods that tests
 * can call to help aid in testing i.e. a login method.  For this
 * demo, we are using a database so we want the to reset the data
 * in the database after each tests run.
 *
 * By extending PHPUnit_Extensions_Database_TestCase, we are given
 * two hooks: getConnection and getDataSet.
 * getCconnection needs to retun an instance of
 * PHPUnit_Extensions_Database_DB_IDatabaseConnection which PHPUnit
 * will use to connect when needed.  getDataSet needs to retun
 * PHPUnit_Extensions_Database_DataSet_IDataSet
 *
 * It is strongly recomeded that you call the parent setUp and
 * tearDown when overriding.  When using PHPUnit_Extensions_Database_TestCase
 * those hooks are vital to resetting the database.
 *
 * PHP version 5.2
 *
 * @category   Test
 * @package    LIPHP
 * @subpackage Controllers
 * @author     Chuck Reeves <chuck.reeves@gmail.com>
 * @version    $Id: $
 * @link       https://code.google.com/p/liphp-manchuck-unit/source/browse/#svn/trunk
 */
class ControllerTestCase
    extends PHPUnit_Extensions_Database_TestCase
{

    /**
     * Here you return a PHPUnit_Extensions_Database_DB_IMetaData
     * Adapter to the parent.
     */
    protected function getConnection()
    {
        $db = new PDO('mysql:host=localhost;dbname=liphp_unit', 'unitUser', '12345');
        return $this->createDefaultDBConnection($db, 'liphp_unit');
    }

    /**
     * This method needs to return instance of
     * PHPUnit_Extensions_Database_DataSet_IDataSet
     * Each test will cause PHP Unit to truncate
     * each table that you set in the data set.
     *
     * So yea do not run tests on a production database
     */
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(DATA_PATH . '/initialFixture.xml');
    }
}