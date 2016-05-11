<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\AccountBundle\Constraint;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEmailValidator extends ConstraintValidator
{
    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippets;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Shopware_Components_Snippet_Manager $snippets
     * @param Connection $connection
     */
    public function __construct(
        Shopware_Components_Snippet_Manager $snippets,
        Connection $connection
    ) {
        $this->snippets = $snippets;
        $this->connection = $connection;
    }

    /**
     * @param string $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEmail) {
            return;
        }

        $builder = $this->createQueryBuilder($value, $constraint->getShop(), $constraint->getCustomerId());

        $exists = $builder->execute()->rowCount() > 0;
        if (!$exists) {
            return;
        }

        $emailMessage = $this->snippets
            ->getNamespace('frontend/account/internalMessages')
            ->get('MailFailureAlreadyRegistered', 'This mail address is already registered');

        $this->context->buildViolation($emailMessage)
            ->atPath($this->context->getPropertyPath())
            ->addViolation();
    }

    /**
     * @param string $value
     * @param Shop $shop
     * @param null|int $customerId
     * @return QueryBuilder
     */
    private function createQueryBuilder($value, Shop $shop, $customerId = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(1);
        $builder->from('s_user');
        $builder->andWhere('email = :email');
        $builder->andWhere('accountmode != 1');
        $builder->setParameter('email', $value);

        if ($shop->hasCustomerScope()) {
            $builder->andWhere('subshopID = :shopId');
            $builder->setParameter('shopId', $shop->getId());
        }

        if ($customerId !== null) {
            $builder->andWhere('id != :userId');
            $builder->setParameter('userId', $customerId);
        }

        return $builder;
    }
}
