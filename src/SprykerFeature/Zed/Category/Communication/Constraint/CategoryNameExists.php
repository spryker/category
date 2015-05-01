<?php

namespace SprykerFeature\Zed\Category\Communication\Constraint;

use SprykerEngine\Shared\Dto\LocaleDto;
use SprykerFeature\Zed\Category\Persistence\CategoryQueryContainer;
use Symfony\Component\Validator\Constraint;

class CategoryNameExists extends Constraint
{

    public $message = 'A category with the name {{ value }} already exists in the Database!';

    /**
     * @var CategoryQueryContainer
     */
    protected $queryContainer;

    /**
     * @var int
     */
    protected $idCategory;

    /**
     * @var LocaleDto
     */
    protected $locale;

    /**
     * @param CategoryQueryContainer $queryContainer
     * @param int $idCategory
     * @param LocaleDto $locale
     * @param mixed $options
     */
    public function __construct(
        CategoryQueryContainer $queryContainer,
        $idCategory,
        LocaleDto $locale,
        $options = null
    ) {
        parent::__construct($options);
        $this->queryContainer= $queryContainer;
        $this->idCategory = $idCategory;
        $this->locale = $locale;
    }

    /**
     * @return CategoryQueryContainer
     */
    public function getQueryContainer()
    {
        return $this->queryContainer;
    }

    /**
     * @return LocaleDto
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function getIdCategory()
    {
        return $this->idCategory;
    }
}
