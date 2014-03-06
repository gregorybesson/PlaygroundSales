<?php

namespace PlaygroundSales\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="sales_payment_method_translation", indexes={
 *      @ORM\Index(name="sales_payment_method_translation", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class PaymentMethodTranslation extends AbstractTranslation
{

}