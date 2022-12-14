<?php

namespace App\Serializer\Denormalizer;

use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Page;
use App\Service\Util;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PageDenormalizer implements DenormalizerInterface
{

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ObjectNormalizer $normalizer,
        protected Util $util
    )
    {}

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        /** @var Page $object */
        $object = $context[AbstractNormalizer::OBJECT_TO_POPULATE] ?? null;

        if ($object) {
            if (isset($data['author'])) {
                $author = $this->entityManager->getRepository(Author::class)->findOneBy([
                    'slug' => $this->util->slugify($data['author'])
                ]);
                if (!$author) {
                    $author = new Author();
                    $author->setFirstName($data['author']);
                    $this->entityManager->persist($author);
                    $this->entityManager->flush();
                }

                $object->setAuthor($author);
            }
        }

        $context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT] = true;
        return $this->normalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return $type === Page::class;
    }
}
