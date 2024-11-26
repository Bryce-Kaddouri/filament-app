<?php

namespace App\Services;

use Google\Cloud\DocumentAI\V1\Document;

class ParsedImage
{
    private $base64Image;
    private $entities;
    private $imageWidth;
    private $imageHeight;

    private $types;

    private $invoiceId;
    private $invoiceDate;

    private $lineItems;

    public function __construct(Document $document)
    {
        $this->types = [
            0 => 'invoice_date',
            1 => 'invoice_id',
            2 => 'line_item',
            3 => 'supplier_name'
        ];
        $this->imageWidth = $document->getPages()[0]->getDimension()->getWidth();
        $this->imageHeight = $document->getPages()[0]->getDimension()->getHeight();

        // Extract base64 image
        $this->base64Image = $this->extractBase64Image($document);

        // Extract and transform entities
        $this->entities = $this->transformEntities($document->getEntities());
    }

    /**
     * Extract the base64 image from the document.
     *
     * @param Document $document
     * @return string|null
     */
    private function extractBase64Image(Document $document): ?string
    {
        foreach ($document->getPages() as $page) {
            if ($page->hasImage()) {
                return base64_encode($page->getImage()->getContent());
            }
        }
        return null;
    }

    /**
     * Transform Document AI entities into a JSON-serializable format.
     *
     * @param \Google\Protobuf\Internal\RepeatedField $entities
     * @return array
     */
    private function transformEntities($entities): array
    {
        $transformedEntities = [];
        $lineItems = [];

        foreach ($entities as $entity) {
            if(in_array($entity->getType(), $this->types)){
                // add in line items if type is line item
                if($entity->getType() == 'line_item'){
                    $properties = $entity->getProperties();
                    // check if properties has type qty
                    $item = [];
                    foreach($properties as $property){
                            if($property->getType() == 'line_item/quantity'){
                                $item['quantity'] = $property->getMentionText();
                            }elseif($property->getType() == 'line_item/unit_price'){
                                $item['unit_price'] = $property->getMentionText();
                            }elseif($property->getType() == 'line_item/product_code'){
                                /* $item['product'] = $property->getMentionText(); */
                                $item['product_code'] = null;
                            }
                        
                    }
                    $lineItems[] = $item;
                }


                if($entity->getType() == 'invoice_date'){
                    $this->invoiceDate = $entity->getMentionText();
                }
                if($entity->getType() == 'invoice_id'){
                    $this->invoiceId = $entity->getMentionText();
                }
            if ($entity->getPageAnchor() && $entity->getPageAnchor()->getPageRefs()->count() > 0) {
                $pageRef = $entity->getPageAnchor()->getPageRefs()[0];
                $boundingPoly = $pageRef->getBoundingPoly();

                if ($boundingPoly) {
                    $absoluteVertices = array_map(function ($vertex) {
                        return [
                            'x' => $vertex->getX() * $this->imageWidth,
                            'y' => $vertex->getY() * $this->imageHeight,
                        ];
                    }, iterator_to_array($boundingPoly->getNormalizedVertices()));

                    $transformedEntities[] = [
                        'type' => $entity->getType(),
                        'mentionText' => $entity->getMentionText(),
                        'absoluteVertices' => $absoluteVertices,
                    ];
                }
            }
            }
        }
        $this->lineItems = $lineItems;
        return $transformedEntities;
    }

    /**
     * Return the class data as a JSON-serializable array.
     *
     * @return array
     */
    public function toJsonSerializable(): array
    {
        return [
            'base64Image' => $this->base64Image,
            'imageWidth' => $this->imageWidth,
            'imageHeight' => $this->imageHeight,
            'entities' => $this->entities,
        ];
    }
    public function getInvoiceDate(){
        return $this->invoiceDate;
    }

    public function getInvoiceId(){
        return $this->invoiceId;
    }

    public function getLineItems(){
        return $this->lineItems;
    }
}
