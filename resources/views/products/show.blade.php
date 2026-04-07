<link rel="stylesheet" href="{{ asset('css/style.css') }}">
 <div class="product">
        <img src="{{asset('storage/' . $product->image)}}" alt="{{ $product->name }}">
        
        <h3>{{ $product->name }}</h3>
        <p>{{ $product->price }} zł</p>
        
        <div class="product-action">
            <a class="see" href="{{ route('products.show', $product) }}">Zobacz</a>
            <a class="edit" href="{{ route('products.edit', $product) }}">Edytuj</a>

            <form action="{{ route('products.destroy', $product) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="delete" type="submit">Usuń</button>
            </form>
        </div>  
         <div>
            <p>OCR: {{ $product->ocr_text }}</p>
            <p><strong>Kolor:</strong> {{ $product->color ?? 'Brak' }}</p>
            <p><strong>Typ ubrania:</strong> {{ $product->type ?? 'Brak' }}</p>
        </div>
</div>