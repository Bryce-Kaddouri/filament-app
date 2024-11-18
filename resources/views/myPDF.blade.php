<!DOCTYPE html>
<html>
<head>
    <title>Laravel 11 Generate PDF Example - ItSolutionStuff.com</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" >
</head>
<body>
    {{ "images count: " . count($images) }}
    @foreach($images as $image)
        <img src="{{ $image->getRealPath() }}" alt="Image">
    @endforeach

  
</body>
</html>