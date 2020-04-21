<?php

namespace App\Controller;

use App\Entity\BlogPost;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Config\Definition\Exception\Exception;



/**
 *@Route("/blog")
 */

class BlogController extends AbstractController
{

    /**
     * Api Ajout Blog POST
     *

     * @Rest\Post("/add", name="blog_add")
     * @param Request $request
     *
     * @return Response
     */
    public function ApiaddBlogAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        $blog = [];
        $message = "";

        try {

            $error = false;

            $title = $request->request->get('title');

            $blog = new BlogPost();
            $blog->setTitle($title);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($blog);
            $entityManager->flush();
        } catch (Exception $ex) {
            $Error = [
                'stats'  => 0,
                'error' => true,
                'message' => "An error has occurred - Error: {$ex->getMessage()}"
            ];

            return new Response($serializer->serialize($Error, "json"));
        }

        $response = [
            'stats' => 1,
            'error' => $error,
            'data' =>  $blog,
        ];

        return new Response($serializer->serialize($response, "json"));
    }


    /*
    /**
     * @Route("/add",name="blog_add",methods={"POST"})
     */
    /*public function add(Request $request)
    {
        /** @var Serializer $serializer */
    /* $serializer = $this->get('serializer');
        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');
        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();

        // return $this->json($blogPost);
        return 'test';
    }

    */
    /*private const POSTS = [
        [
            'id' => 1,
            'slug' => 'hello-world',
            'title' => 'Hello World!'
        ],
        [
            'id' => 2,
            'slug' => 'another-post',
            'title' => 'this is another post !'
        ],
        [
            'id' => 3,
            'slug' => 'last-example',
            'title' => 'This is the last exapmle !'
        ]
    ];*/
    /**
     * @Route("/{page}", name="blog_list", defaults={"page"=5},requirements={"page"="\d+"})
     */
    public function list($page = 1, Request $request)
    {
        $limit = $request->get('limit', 10);
        $repository = $this->getDoctrine()->getRepository(BlogPost::class);
        $items = $repository->findAll();
        // return new Response($limit);
        return $this->json(
            [
                'page' => $page,
                'limit' => $limit,
                'data' => array_map(function ($item) {
                    return $this->generateUrl('blog_by_slug', ['slug' => $item->getSlug()]);
                }, $items)

            ]
        );
        /*  return $this->generateUrl('blog_by_slug', ['slug' => $item['slug']]);
                }, self::POSTS)*/
    }
    /**
     * @Route("/post/{id}", name="blog_by_id", requirements={"id"="\d+"}, methods={"GET"})
     * @ParamConverter("post", class="App:BlogPost")
     */
    public function post($post)
    {
        // It's the same as doing find($id) on repository
        return $this->json($post);
        // return $this->json($this->getDoctrine()->getRepository(BlogPost::class)->findBy($id));
        //array_search($id, array_column(self::POSTS, 'id'))
    }
    /**
     * @Route("/post/{slug}", name="blog_by_slug", methods={"GET"})
     * The below annotation is not required when $post is typehinted with BlogPost
     * and route parameter name matches any field on the BlogPost entity
     * @ParamConverter("post", class="App:BlogPost", options={"mapping": {"slug": "slug"}})
     */
    public function postbyslug(BlogPost $post)
    {
        // Same as doing findOneBy(['slug' => contents of {slug}])
        return $this->json($post);
        //  return $this->json($this->getDoctrine()->getRepository(BlogPost::class)->findBy(['slug' => $slug]));
    }

    /**
     * @Route("/post/{id}", name="blog_delete", methods={"DELETE"})
     */
    public function delete(BlogPost $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
