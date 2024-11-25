<?php

namespace App\Form;

use App\Entity\Mix;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MixFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('title')
            ->add('description')
            ->add('tracklist')
            ->add('audio', FileType::class, [
                'label' => 'File (MP3 file)',
                'help' => 'Max 500MB',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the MP3 file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '500M',
                        'mimeTypes' => [
                            'audio/mpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid mp3 file',
                    ])
                ],
            ])
            ->add('cover', FileType::class, [
                'label' => 'File (JPG or PNG file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the JPG file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid jpg or png file',
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Upload Mix',
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mix::class,
        ]);
    }
}
