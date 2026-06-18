<div class="mx-auto flex min-h-screen max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/80 shadow-[0_24px_80px_-32px_rgba(15,23,42,0.35)] backdrop-blur">
        <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.2fr_0.8fr] lg:px-10">
            <div class="space-y-5">
                <p class="inline-flex rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-teal-700">
                    Realizado por Camila Calvopiaña
                </p>
                <div class="space-y-3">
                    <h1 class="max-w-3xl font-serif text-4xl leading-tight text-slate-900 sm:text-5xl">
                        Centro de integracion para <span class="text-teal-700">products</span> y <span class="text-amber-700">carts</span>
                    </h1>
                    <p class="max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                        La interfaz trabaja sobre una fachada interna unificada que centraliza products y carts.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button wire:click="refreshDashboard" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                        Recargar dashboard
                    </button>
                    <a href="https://fakestoreapi.com/docs#tag/Products" target="_blank" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-900">
                        Ver API products
                    </a>
                    <a href="https://fakestoreapi.com/docs#tag/Carts" target="_blank" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-900">
                        Ver API carts
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-[1.75rem] border border-teal-100 bg-teal-50/80 p-5">
                    <p class="text-sm font-medium text-teal-800">Productos cargados</p>
                    <p class="mt-3 text-4xl font-semibold text-teal-950">{{ count($products) }}</p>
                    <p class="mt-2 text-sm text-teal-700">Listado sincronizado desde el servicio `ProductService`.</p>
                </div>
                <div class="rounded-[1.75rem] border border-amber-100 bg-amber-50/80 p-5">
                    <p class="text-sm font-medium text-amber-800">Carritos cargados</p>
                    <p class="mt-3 text-4xl font-semibold text-amber-950">{{ count($carts) }}</p>
                    <p class="mt-2 text-sm text-amber-700">Listado sincronizado desde el servicio `CartService`.</p>
                </div>
            </div>
        </div>
    </section>

    @if ($successMessage)
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800 shadow-sm">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800 shadow-sm">
            {{ $errorMessage }}
        </div>
    @endif

    <section class="grid gap-8 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            <div class="flex flex-wrap gap-3">
                <button wire:click="setSection('products')" class="{{ $activeSection === 'products' ? 'bg-slate-900 text-white' : 'border border-slate-300 text-slate-700' }} rounded-full px-5 py-2.5 text-sm font-semibold transition">
                    Products
                </button>
                <button wire:click="setSection('carts')" class="{{ $activeSection === 'carts' ? 'bg-slate-900 text-white' : 'border border-slate-300 text-slate-700' }} rounded-full px-5 py-2.5 text-sm font-semibold transition">
                    Carts
                </button>
            </div>

            @if ($activeSection === 'products')
                <div class="rounded-[2rem] border border-slate-200 bg-white/85 p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="font-serif text-2xl text-slate-900">Products workspace</h2>
                            <p class="mt-1 text-sm text-slate-500">Consulta por ID, crea registros o actualiza uno existente.</p>
                        </div>
                        <button wire:click="newProduct" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-900">
                            Nuevo producto
                        </button>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <input wire:model="productLookupId" type="number" min="1" placeholder="ID de producto" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-teal-500 focus:bg-white">
                        <button wire:click="findProduct" class="rounded-2xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-700">
                            Buscar producto
                        </button>
                    </div>
                    @error('productLookupId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <label class="space-y-2 text-sm text-slate-600">
                            <span>ID</span>
                            <input wire:model="productForm.id" type="number" min="1" placeholder="Opcional para update" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:bg-white">
                            @error('productForm.id') <p class="text-rose-600">{{ $message }}</p> @enderror
                        </label>
                        <label class="space-y-2 text-sm text-slate-600">
                            <span>Titulo</span>
                            <input wire:model="productForm.title" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:bg-white">
                            @error('productForm.title') <p class="text-rose-600">{{ $message }}</p> @enderror
                        </label>
                        <label class="space-y-2 text-sm text-slate-600">
                            <span>Precio</span>
                            <input wire:model="productForm.price" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:bg-white">
                            @error('productForm.price') <p class="text-rose-600">{{ $message }}</p> @enderror
                        </label>
                        <label class="space-y-2 text-sm text-slate-600">
                            <span>Categoria</span>
                            <input wire:model="productForm.category" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:bg-white">
                            @error('productForm.category') <p class="text-rose-600">{{ $message }}</p> @enderror
                        </label>
                        <label class="space-y-2 text-sm text-slate-600 md:col-span-2">
                            <span>Descripcion</span>
                            <textarea wire:model="productForm.description" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:bg-white"></textarea>
                            @error('productForm.description') <p class="text-rose-600">{{ $message }}</p> @enderror
                        </label>
                        <label class="space-y-2 text-sm text-slate-600 md:col-span-2">
                            <span>Imagen</span>
                            <input wire:model="productForm.image" type="url" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:bg-white">
                            @error('productForm.image') <p class="text-rose-600">{{ $message }}</p> @enderror
                        </label>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button wire:click="saveProduct" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                            Guardar producto
                        </button>
                        @if (! empty($productForm['id']))
                            <button wire:click="deleteProduct({{ (int) $productForm['id'] }})" class="rounded-full bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                                Eliminar producto
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-[2rem] border border-slate-200 bg-white/85 p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="font-serif text-2xl text-slate-900">Carts workspace</h2>
                            <p class="mt-1 text-sm text-slate-500">Gestiona carritos y sus productos desde el mismo wrapper.</p>
                        </div>
                        <button wire:click="newCart" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-900">
                            Nuevo carrito
                        </button>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <input wire:model="cartLookupId" type="number" min="1" placeholder="ID de carrito" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-amber-500 focus:bg-white">
                        <button wire:click="findCart" class="rounded-2xl bg-amber-500 px-5 py-3 text-sm font-semibold text-amber-950 transition hover:bg-amber-400">
                            Buscar carrito
                        </button>
                    </div>
                    @error('cartLookupId') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <label class="space-y-2 text-sm text-slate-600">
                            <span>ID</span>
                            <input wire:model="cartForm.id" type="number" min="1" placeholder="Opcional para update" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-amber-500 focus:bg-white">
                            @error('cartForm.id') <p class="text-rose-600">{{ $message }}</p> @enderror
                        </label>
                        <label class="space-y-2 text-sm text-slate-600">
                            <span>User ID</span>
                            <input wire:model="cartForm.userId" type="number" min="1" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-amber-500 focus:bg-white">
                            @error('cartForm.userId') <p class="text-rose-600">{{ $message }}</p> @enderror
                        </label>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="font-semibold text-slate-900">Productos del carrito</h3>
                            <button wire:click="addCartProductRow" class="rounded-full border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-500 hover:text-amber-900">
                                Agregar producto
                            </button>
                        </div>

                        @foreach ($cartForm['products'] as $index => $product)
                            <div wire:key="cart-product-{{ $index }}" class="rounded-[1.5rem] border border-slate-200 bg-slate-50/80 p-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="space-y-2 text-sm text-slate-600">
                                        <span>ID producto</span>
                                        <input wire:model="cartForm.products.{{ $index }}.id" type="number" min="1" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500">
                                        @error('cartForm.products.'.$index.'.id') <p class="text-rose-600">{{ $message }}</p> @enderror
                                    </label>
                                    <label class="space-y-2 text-sm text-slate-600">
                                        <span>Cantidad</span>
                                        <input wire:model="cartForm.products.{{ $index }}.quantity" type="number" min="1" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500">
                                        @error('cartForm.products.'.$index.'.quantity') <p class="text-rose-600">{{ $message }}</p> @enderror
                                    </label>
                                    <label class="space-y-2 text-sm text-slate-600">
                                        <span>Titulo</span>
                                        <input wire:model="cartForm.products.{{ $index }}.title" type="text" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500">
                                        @error('cartForm.products.'.$index.'.title') <p class="text-rose-600">{{ $message }}</p> @enderror
                                    </label>
                                    <label class="space-y-2 text-sm text-slate-600">
                                        <span>Precio</span>
                                        <input wire:model="cartForm.products.{{ $index }}.price" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500">
                                        @error('cartForm.products.'.$index.'.price') <p class="text-rose-600">{{ $message }}</p> @enderror
                                    </label>
                                    <label class="space-y-2 text-sm text-slate-600">
                                        <span>Categoria</span>
                                        <input wire:model="cartForm.products.{{ $index }}.category" type="text" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500">
                                        @error('cartForm.products.'.$index.'.category') <p class="text-rose-600">{{ $message }}</p> @enderror
                                    </label>
                                    <label class="space-y-2 text-sm text-slate-600">
                                        <span>Imagen</span>
                                        <input wire:model="cartForm.products.{{ $index }}.image" type="url" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500">
                                        @error('cartForm.products.'.$index.'.image') <p class="text-rose-600">{{ $message }}</p> @enderror
                                    </label>
                                    <label class="space-y-2 text-sm text-slate-600 md:col-span-2">
                                        <span>Descripcion</span>
                                        <textarea wire:model="cartForm.products.{{ $index }}.description" rows="3" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none transition focus:border-amber-500"></textarea>
                                        @error('cartForm.products.'.$index.'.description') <p class="text-rose-600">{{ $message }}</p> @enderror
                                    </label>
                                </div>

                                <div class="mt-4">
                                    <button wire:click="removeCartProductRow({{ $index }})" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:border-rose-400 hover:text-rose-900">
                                        Quitar fila
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button wire:click="saveCart" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                            Guardar carrito
                        </button>
                        @if (! empty($cartForm['id']))
                            <button wire:click="deleteCart({{ (int) $cartForm['id'] }})" class="rounded-full bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                                Eliminar carrito
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white/85 p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="font-serif text-2xl text-slate-900">Coleccion live</h2>
                        <p class="mt-1 text-sm text-slate-500">Resumen rápido de respuestas ya transformadas por los DTOs response.</p>
                    </div>
                </div>

                @if ($activeSection === 'products')
                    <div class="mt-6 space-y-4">
                        @forelse ($products as $product)
                            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50/70 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Product #{{ $product['id'] }}</p>
                                        <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $product['title'] }}</h3>
                                        <p class="mt-2 text-sm text-slate-600">{{ $product['category'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-semibold text-teal-700">${{ number_format((float) $product['price'], 2) }}</p>
                                    </div>
                                </div>
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $product['description'] }}</p>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <button wire:click="editProduct({{ $product['id'] }})" class="rounded-full border border-teal-200 px-4 py-2 text-sm font-semibold text-teal-700 transition hover:border-teal-500 hover:text-teal-900">
                                        Editar
                                    </button>
                                    <button wire:click="deleteProduct({{ $product['id'] }})" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:border-rose-400 hover:text-rose-900">
                                        Eliminar
                                    </button>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-3xl border border-dashed border-slate-300 px-5 py-8 text-center text-sm text-slate-500">
                                No hay productos cargados todavia.
                            </p>
                        @endforelse
                    </div>
                @else
                    <div class="mt-6 space-y-4">
                        @forelse ($carts as $cart)
                            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50/70 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Cart #{{ $cart['id'] }}</p>
                                        <h3 class="mt-2 text-lg font-semibold text-slate-900">Usuario {{ $cart['userId'] }}</h3>
                                        <p class="mt-2 text-sm text-slate-600">{{ count($cart['products']) }} producto(s) asociados</p>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    @foreach ($cart['products'] as $product)
                                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                            <span class="font-semibold text-slate-900">#{{ $product['id'] }}</span>
                                            {{ $product['title'] ?: 'Sin titulo recibido por el API' }}
                                            <span class="ml-2 text-slate-400">x {{ $product['quantity'] ?? 1 }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <button wire:click="editCart({{ $cart['id'] }})" class="rounded-full border border-amber-200 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-500 hover:text-amber-900">
                                        Editar
                                    </button>
                                    <button wire:click="deleteCart({{ $cart['id'] }})" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:border-rose-400 hover:text-rose-900">
                                        Eliminar
                                    </button>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-3xl border border-dashed border-slate-300 px-5 py-8 text-center text-sm text-slate-500">
                                No hay carritos cargados todavia.
                            </p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
