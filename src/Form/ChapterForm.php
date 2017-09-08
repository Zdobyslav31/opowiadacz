<?php
/**
 * Chapter type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChapterType.
 *
 * @package Form
 */
class ChapterForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'label.title',
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'max_length' => 50,
                    ],
                    'constraints' => [
                        new Assert\NotBlank(
                            ['groups' => ['chapter-default']]
                        ),
                        new Assert\Length(
                            [
                            'groups' => ['chapter-default'],
                            'min' => 3,
                            'max' => 50,
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'intro',
                TextareaType::class,
                [
                    'label' => 'label.intro',
                    'required' => true,
                    'attr' => [
                        'max_length' => 1000,
                        'class' => 'form-control',
                        'rows' => 4,
                        'placeholder' => 'info.intro',
                    ],
                    'constraints' => [
                        new Assert\NotBlank(
                            ['groups' => ['chapter-default']]
                        ),
                        new Assert\Length(
                            [
                                'groups' => ['chapter-default'],
                                'min' => 10,
                                'max' => 1000,
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'content',
                TextareaType::class,
                [
                    'label' => 'label.content',
                    'required' => true,
                    'attr' => [
                        'max_length' => 50000,
                        'class' => 'form-control mceEditor',
                        'rows' => 6,
                    ],
                    'constraints' => [
                        new Assert\NotBlank(
                            ['groups' => ['chapter-default']]
                        ),
                        new Assert\Length(
                            [
                                'groups' => ['chapter-default'],
                                'min' => 30,
                                'max' => 50000,
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'summary',
                TextareaType::class,
                [
                    'label' => 'label.summary',
                    'required' => true,
                    'attr' => [
                        'max_length' => 2000,
                        'class' => 'form-control',
                        'rows' => 5,
                        'placeholder' => 'info.summary',
                    ],
                    'constraints' => [
                        new Assert\NotBlank(
                            ['groups' => ['chapter-default']]
                        ),
                        new Assert\Length(
                            [
                                'groups' => ['chapter-default'],
                                'min' => 10,
                                'max' => 2000,
                            ]
                        ),
                    ],
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'chapter_form';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'chapter-default',
            ]
        );
    }
}
