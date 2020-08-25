<?php
namespace App\Test\TestCase\Model\Behavior;

use App\Model\Behavior\LimitResultsBehavior;
use Cake\TestSuite\TestCase;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class LimitResultsBehaviorTest extends TestCase
{
    private $query;
    private $behavior;

    public $fixtures = [
        'app.Sentences',
        'app.Users',
    ];

    public function setUp()
    {
        parent::setUp();

        $s = TableRegistry::getTableLocator()->get('Sentences');
        $this->behavior = new LimitResultsBehavior($s);
        // PHPUnit annoying warnings silenced
        // Please someone check if this gets fixed in version 7.0 or newer
        $this->query = @$this->createTestProxy(Query::class, [$s->getConnection(), $s]);
        $this->query->order(['Sentences.id' => 'DESC']);
    }

    public function tearDown()
    {
        unset($this->query);
        unset($this->behavior);

        parent::tearDown();
    }

    public function testFindLatest_simple()
    {
        $this->query
             ->expects($this->once())
             ->method('where')
             ->with(['Sentences.id >=' => 38]);

        $this->behavior->findLatest($this->query, ['maxResults' => 20]);
    }

    public function testFindLatest_whereOnMainTable()
    {
        $this->query
             ->where(['Sentences.lang' => 'cmn']);

        $this->query
             ->expects($this->once())
             ->method('where')
             ->with(['Sentences.id >=' => 2]);

        $this->behavior->findLatest($this->query, ['maxResults' => 20]);
    }

    public function testFindLatest_whereOnJoinedTable()
    {
        $this->query
             ->where(['Users.role' => 'contributor'])
             ->contain(['Users']);

        $this->query
             ->expects($this->once())
             ->method('where')
             ->with(['Sentences.id >=' => 11]);

        $this->behavior->findLatest($this->query, ['maxResults' => 20]);
    }

    public function testFindLatest_whereIsNullOnMainTable()
    {
        $this->query
             ->where(['Sentences.lang IS' => null]);

        $this->query
             ->expects($this->once())
             ->method('where')
             ->with(['Sentences.id >=' => 9]);

        $this->behavior->findLatest($this->query, ['maxResults' => 20]);
    }
}
