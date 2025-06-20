<?php namespace Tests;
use App\Models\Foundation\Main\IGroup;
use LaravelDoctrine\ORM\Facades\EntityManager;
use models\main\AuditLog;
use models\main\Member;
use models\main\SummitAuditLog;
use models\main\SummitEventAuditLog;
use models\summit\Summit;
use utils\Filter;
use utils\FilterParser;
use utils\Order;
use utils\OrderElement;
use utils\PagingInfo;

/**
 * Copyright 2022 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

/**
 * Class AuditModelTest
 */
class AuditModelTest extends ProtectedApiTestCase
{
      use InsertSummitTestData;

    use InsertMemberTestData;

    protected function setUp():void
    {
        parent::setUp();
        self::insertMemberTestData(IGroup::TrackChairs);
        self::$defaultMember = self::$member;
        self::insertSummitTestData();
    }

    protected function tearDown():void
    {
        self::clearSummitTestData();
        self::clearMemberTestData();
        parent::tearDown();
    }

    public function testAuditSummitChange(){
        $audit_repository = EntityManager::getRepository(AuditLog::class);

        $summit_audit = new SummitAuditLog(
            self::$defaultMember,
            'from Summit [SNAPSHOT N] to Summit [SNAPSHOT N + 1]',
            self::$summit
        );

        self::$em->persist($summit_audit);
        self::$em->flush();

        $filter = FilterParser::parse(
            ["filter" => "class_name==" . SummitAuditLog::ClassName],
            ["class_name" => ['=@', '==']]
        );

        $order = new Order([
            OrderElement::buildDescFor("id"),
        ]);

        $retrieved_summit_audit =
            $audit_repository->getAllByPage(new PagingInfo(1, 5), $filter, $order)->getItems()[0];
        self::assertNotEmpty($retrieved_summit_audit->getAction());
        self::assertEquals(SummitAuditLog::ClassName, $retrieved_summit_audit->getClassName());
        self::assertEquals(self::$defaultMember->getId(), $retrieved_summit_audit->getUser()->getId());
        self::assertEquals(self::$summit->getId(), $retrieved_summit_audit->getSummit()->getId());
    }

    public function testAuditSummitEventChange(){
        $audit_repository = EntityManager::getRepository(AuditLog::class);

        $summit_event = self::$summit->getEvents()[0];

        $summit_audit = new SummitEventAuditLog(
            self::$defaultMember,
            'from SummitEvent [SNAPSHOT N] to SummitEvent [SNAPSHOT N + 1]',
            self::$summit,
            $summit_event
        );

        self::$em->persist($summit_audit);
        self::$em->flush();

        $filter = FilterParser::parse(
            ["filter" => "class_name==" . SummitEventAuditLog::ClassName],
            ["class_name" => ['=@', '==']]
        );

        $order = new Order([
            OrderElement::buildDescFor("id"),
        ]);

        $retrieved_summit_audit =
            $audit_repository->getAllByPage(new PagingInfo(1, 5), $filter, $order)->getItems()[0];
        self::assertNotEmpty($retrieved_summit_audit->getAction());
        self::assertEquals(SummitEventAuditLog::ClassName, $retrieved_summit_audit->getClassName());
        self::assertEquals(self::$defaultMember->getId(), $retrieved_summit_audit->getUser()->getId());
    }
}