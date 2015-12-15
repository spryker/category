<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Category\Communication\Form;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Category\Persistence\CategoryQueryContainer;
use Spryker\Zed\Propel\Business\Formatter\PropelArraySetFormatter;
use Spryker\Zed\Gui\Communication\Form\AbstractForm;
use Symfony\Component\Validator\Constraints;

class CategoryNodeForm extends AbstractForm
{

    const IS_ROOT = 'is_root';
    const FK_CATEGORY = 'fk_category';
    const FK_PARENT_CATEGORY_NODE = 'fk_parent_category_node';
    const ID_CATEGORY_NODE = 'id_category_node';
    const CATEGORY_NAME = 'category_name';
    const PARENT_CATEGORY_NAME = 'parent_category_name';

    /**
     * @return void
     */
    protected function buildFormFields()
    {
        // @todo: Implement buildFormFields() method.
    }

    /**
     * @return void
     */
    protected function populateFormFields()
    {
        // @todo: Implement populateFormFields() method.
    }

    /**
     * @var LocaleTransfer
     */
    protected $localeTransfer;

    /**
     * @var CategoryQueryContainer
     */
    protected $queryContainer;

    /**
     * @param LocaleTransfer $localeTransfer
     */
    public function __construct(LocaleTransfer $localeTransfer)
    {
        $this->localeTransfer = $localeTransfer;
    }

    /**
     * @return array
     */
    protected function getDefaultData()
    {
        $nodeEntity = $this->queryContainer
            ->queryNodeById($this->getCategoryIdNode())
            ->findOne();

        if ($nodeEntity !== null) {
            return $nodeEntity->toArray();
        }

        return [];
    }

    /**
     * @return void
     */
    public function addFormFields()
    {
        $this->addField(self::ID_CATEGORY_NODE);
        $this->addField(self::IS_ROOT)
            ->setRefresh(true)
            ->setConstraints([
                new Constraints\Type([
                    'type' => 'bool',
                ]),
            ]);
        $this->addField(self::FK_CATEGORY)
            ->setAccepts($this->getCategories())
            ->setRefresh(false)
            ->setConstraints([
                new Constraints\Type([
                    'type' => 'integer',
                ]),
                new Constraints\Choice([
                    'choices' => array_column($this->getCategories(), 'value'),
                    'message' => 'Please choose one of the given Categories',
                ]),
            ])
            ->setValueHook(function ($value) {
                return $value ? (int) $value : null;
            });

        $this->addField(self::FK_PARENT_CATEGORY_NODE)
            ->setAccepts($this->getParentCategories())
            ->setRefresh(false)
            ->setConstraints([
                new Constraints\Type([
                    'type' => 'integer',
                ]),
                new Constraints\Choice([
                    'choices' => array_column($this->getParentCategories(), 'value'),
                    'message' => 'Please choose one of the given Parent Categories',
                ]),
                new Constraints\NotBlank(),
            ])
            ->setValueHook(function ($value) {
                return $value ? (int) $value : null;
            });
    }

    /**
     * @return array
     */
    protected function getCategories()
    {
        $categories = $this->queryContainer
            ->queryCategory($this->localeTransfer->getIdLocale())
            ->setFormatter(new PropelArraySetFormatter())
            ->find();

        $data = [];
        foreach ($categories as $category) {
            $data[] = $this->formatOption(
                (int) $category['id_category'],
                $category['name']
            );
        }

        if (empty($data)) {
            $data[] = $this->formatOption('', '');
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getParentCategories()
    {
        $categoryNodes = $this->queryContainer
            ->queryCategoryNode($this->localeTransfer->getIdLocale())
            ->setFormatter(new PropelArraySetFormatter())
            ->find();

        $data = [];
        foreach ($categoryNodes as $categoryNode) {
            $data[] = $this->formatOption(
                (int) $categoryNode[self::ID_CATEGORY_NODE],
                $categoryNode[self::CATEGORY_NAME]
            );
        }

        if (empty($data)) {
            $data[] = $this->formatOption('', '');
        }

        return $data;
    }

    /**
     * @param string $option
     * @param string $label
     *
     * @return array
     */
    protected function formatOption($option, $label)
    {
        return [
            'value' => $option,
            'label' => $label,
        ];
    }

    /**
     * @return int
     */
    protected function getCategoryIdNode()
    {
        return $this->stateContainer->getRequestValue(self::ID_CATEGORY_NODE);
    }

}
