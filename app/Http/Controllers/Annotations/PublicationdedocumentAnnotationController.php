<?php

namespace App\Http\Controllers\Annotations ;

/**
 * @OA\Security(
 *     security={
 *         "BearerAuth": {}
 *     }),

 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"),

 * @OA\Info(
 *     title="Your API Title",
 *     description="Your API Description",
 *     version="1.0.0"),

 * @OA\Consumes({
 *     "multipart/form-data"
 * }),

 *

 * @OA\GET(
 *     path="/api/document-types",
 *     summary="reading alll document types",
 *     description="",
 *         security={
 *    {       "BearerAuth": {}}
 *         },
 * @OA\Response(response="200", description="OK"),
 * @OA\Response(response="404", description="Not Found"),
 * @OA\Response(response="500", description="Internal Server Error"),
 *     @OA\Parameter(in="header", name="User-Agent", required=false, @OA\Schema(type="string")
 * ),
 *     tags={"Publication de document"},
*),


 * @OA\GET(
 *     path="/api/document",
 *     summary="reading alll publication",
 *     description="",
 *         security={
 *    {       "BearerAuth": {}}
 *         },
 * @OA\Response(response="200", description="OK"),
 * @OA\Response(response="404", description="Not Found"),
 * @OA\Response(response="500", description="Internal Server Error"),
 *     @OA\Parameter(in="header", name="User-Agent", required=false, @OA\Schema(type="string")
 * ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 properties={
 *                     @OA\Property(property="image", type="string"),
 *                     @OA\Property(property="OwnerFirstName", type="string"),
 *                     @OA\Property(property="OwnerLastName", type="string"),
 *                     @OA\Property(property="Location", type="string"),
 *                     @OA\Property(property="statut", type="string"),
 *                     @OA\Property(property="document_type_id", type="integer"),
 *                 },
 *             ),
 *         ),
 *     ),
 *     tags={"Publication de document"},
*),


 * @OA\POST(
 *     path="/api/documents",
 *     summary="create a publication",
 *     description="",
 *         security={
 *    {       "BearerAuth": {}}
 *         },
 * @OA\Response(response="201", description="Created successfully"),
 * @OA\Response(response="400", description="Bad Request"),
 * @OA\Response(response="401", description="Unauthorized"),
 * @OA\Response(response="403", description="Forbidden"),
 *     @OA\Parameter(in="header", name="User-Agent", required=false, @OA\Schema(type="string")
 * ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 properties={
 *                     @OA\Property(property="Location", type="string"),
 *                     @OA\Property(property="image", type="string", format="binary"),
 *                     @OA\Property(property="document_type_id", type="string"),
 *                     @OA\Property(property="statut", type="string"),
 *                     @OA\Property(property="OwnerFirstName", type="string"),
 *                     @OA\Property(property="OwnerLastName", type="string"),
 *                 },
 *             ),
 *         ),
 *     ),
 *     tags={"Publication de document"},
*),


 * @OA\PUT(
 *     path="/api/documents/6",
 *     summary="Update a publication",
 *     description="",
 *         security={
 *    {       "BearerAuth": {}}
 *         },
 * @OA\Response(response="200", description="OK"),
 * @OA\Response(response="404", description="Not Found"),
 * @OA\Response(response="500", description="Internal Server Error"),
 *     @OA\Parameter(in="header", name="User-Agent", required=false, @OA\Schema(type="string")
 * ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 properties={
 *                     @OA\Property(property="Location", type="string"),
 *                     @OA\Property(property="image", type="string", format="binary"),
 *                     @OA\Property(property="document_type_id", type="string"),
 *                     @OA\Property(property="statut", type="string"),
 *                     @OA\Property(property="OwnerFirstName", type="string"),
 *                     @OA\Property(property="OwnerLastName", type="string"),
 *                 },
 *             ),
 *         ),
 *     ),
 *     tags={"Publication de document"},
*),


 * @OA\DELETE(
 *     path="/api/documents/5",
 *     summary="deleting a publication by admin",
 *     description="",
 *         security={
 *    {       "BearerAuth": {}}
 *         },
 * @OA\Response(response="204", description="Deleted successfully"),
 * @OA\Response(response="401", description="Unauthorized"),
 * @OA\Response(response="403", description="Forbidden"),
 * @OA\Response(response="404", description="Not Found"),
 *     @OA\Parameter(in="header", name="User-Agent", required=false, @OA\Schema(type="string")
 * ),
 *     tags={"Publication de document"},
*),


*/

 class PublicationdedocumentAnnotationController {}