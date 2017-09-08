<?php
/**
 * Register form.
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Validator\Constraints as CustomAssert;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegisterLoginType
 *
 * @package Form
 */
class RegisterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'user-default',
                'user_repository' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'login',
            TextType::class,
            [
                'label' => 'label.login',
                'required' => true,
                'attr' => [
                    'max_length' => 32,
                    'class' => 'form-control',

                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'groups' => ['user-default'],
                    ]),
                    new Assert\Length(
                        [
                            'groups' => ['user-default'],
                            'min' => 4,
                            'max' => 32,
                        ]
                    ),

                    new CustomAssert\UniqueUsername(
                        [
                            'groups' => ['user-default'],
                            'repository' => isset($options['user_repository']) ? $options['user_repository'] : null,
                            'elementId' => isset($options['data']['id']) ? $options['data']['id'] : null,
                        ]
                    ),
                ],
            ]
        );
        $builder->add(
            'password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'label.password',
                    'required' => true,
                    'attr' => [
                        'max_length' => 32,
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new Assert\NotBlank([
                        'groups' => ['user-default'], ]),
                        new Assert\Length(
                            [
                                'groups' => ['user-default'],
                                'min' => 8,
                                'max' => 32,
                            ]
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => 'label.repeat_password',
                    'required' => true,
                    'attr' => [
                        'max_length' => 32,
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new Assert\NotBlank([
                        'groups' => ['user-default'], ]),
                        new Assert\Length(
                            [
                                'groups' => ['user-default'],
                                'min' => 8,
                                'max' => 32,
                            ]
                        ),
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'register_type';
    }
}
