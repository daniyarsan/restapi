<?php

namespace App\Controller;

use App\Business\Page\PageCreator;
use App\Business\Page\PageDeleter;
use App\Business\Page\PageUpdater;
use App\Entity\Page;
use App\Helper\ExceptionHandlingHelper;
use App\Helper\ListResponseHelper;
use App\Repository\PageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: "/pages/api", name: "pages_api_")]
class PageController extends AbstractController
{
    use ExceptionHandlingHelper;

    public function __construct(
        private PageRepository $pages,
        private SerializerInterface $serializer
    ) {}

    #[Route('/list', name: 'list', methods: ["GET"])]
    public function all(
        Request $request,
        ListResponseHelper $listResponseHelper): Response
    {
        $query = $this->pages->getPageQuery($request);

        return $listResponseHelper->getResponse($request, $query, 'user');
    }


    #[Route('/get/{id}', name: 'get', methods: ["GET"])]
    #[Entity(Page::class, expr: "repository.findActiveOneById(id, 'p')")]
    public function get(Page $page): Response
    {
        return $this->json(
            $page,
            Response::HTTP_OK,
            [],
            ['groups' => 'user']
        );
    }

    #[Route('/create', name: 'create', methods: ["POST"])]
    public function create(Request $request, PageCreator $creator): Response
    {
        $result = $creator->create($request);

        $options = [];
        $status  = Response::HTTP_BAD_REQUEST;
        if ($result instanceof Page) {
            $options['groups'] = 'user';
            $status = Response::HTTP_CREATED;
        }

        return $this->json(
            $result,
            $status,
            [],
            $options
        );
    }

    #[Route('/update/{id}', name: 'update', methods: ["POST"])]
    #[ParamConverter('page', Page::class)]
    public function update(
        Page $page,
        Request $request,
        PageUpdater $pageUpdater): Response
    {
        $result = $pageUpdater->update($request, $page);

        $options = [];
        $status = Response::HTTP_BAD_REQUEST;
        if ($result instanceof Page) {
            $options['groups'] = 'user';
            $status = Response::HTTP_OK;
        }

        return $this->json(
            $result,
            $status,
            [],
            $options
        );
    }

    #[Route('/delete/{id}', name: 'delete', methods: ["POST"])]
    #[ParamConverter('page', Page::class)]
    public function delete(
        Page $page,
        PageDeleter $pageDeleter): Response
    {
        try{
            $pageDeleter->delete($page);
        } catch (\Exception $exception){
            return $this->exceptionHandle($exception);
        }

        return $this->json(
            [],
            Response::HTTP_NO_CONTENT,
            []
        );
    }
}