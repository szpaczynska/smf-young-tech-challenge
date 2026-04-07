<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<h1>Edytuj produkt</h1>

@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    Nazwa:<br>
    <input type="text" name="name" value="{{ old('name', $product->name) }}" required><br>

    Opis:<br>
    <textarea name="description">{{ old('description', $product->description) }}</textarea><br>

    Cena:<br>
    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required><br>

    @if ($product->image)
        Aktualne zdjęcie:<br>
        <img src="{{ asset('storage/' . $product->image) }}"><br>
    @endif

    Zmień zdjęcie:<br>
    <input type="file" name="image"><br><br>

    <button type="submit">Zaktualizuj</button>
</form>