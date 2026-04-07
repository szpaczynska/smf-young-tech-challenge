<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<h1>Dodaj produkt</h1>

@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    Nazwa:<br>
    <input type="text" name="name" required><br>

    Opis:<br>
    <textarea name="description"></textarea><br>

    Cena:<br>
    <input type="number" step="0.01" name="price" required><br>

    Zdjęcie:<br>
    <input type="file" name="image"><br><br>

    <button type="submit">Zapisz</button>
</form>
