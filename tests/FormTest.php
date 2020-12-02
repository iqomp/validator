<?php
declare(strict_types=1);

namespace Iqomp\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Validator\Validator;
use Iqomp\Validator\Form;
use Iqomp\Validator\FormNotRegisteredException;

class FormTest extends TestCase
{
    public function testFormNotFound(){
        $this->expectException(FormNotRegisteredException::class);
        $form = new Form('non-exists-form');
    }

    public function testResultOnError(){
        $form   = new Form('std-name');
        $object = (object)['a' => 'b'];
        $result = $form->validate($object);

        $this->assertNull($result);
    }

    public function testErrorOnError(){
        $form   = new Form('std-name');
        $object = (object)['a' => 'b'];
        $result = $form->validate($object);
        $errors = $form->getErrors();

        $this->assertArrayHasKey('name', $errors);
    }

    public function testResultOnSuccess(){
        $form   = new Form('std-name');
        $object = (object)['name' => 'User'];
        $result = $form->validate($object);

        $this->assertObjectHasAttribute('name', $result);
    }
}
