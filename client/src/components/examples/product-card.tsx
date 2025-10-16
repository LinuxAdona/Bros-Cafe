import { ProductCard } from '../product-card';

export default function ProductCardExample() {
  return (
    <div className="p-6 max-w-xs">
      <ProductCard
        id={1}
        name="Espresso"
        price={3.50}
        image="https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=400"
        category="Coffee"
        stock={45}
        onAddToCart={(id) => console.log('Added product', id)}
      />
    </div>
  );
}
