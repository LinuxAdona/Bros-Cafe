import { CartItem } from '../cart-item';

export default function CartItemExample() {
  return (
    <div className="p-6 max-w-md">
      <CartItem
        id={1}
        name="Cappuccino"
        price={4.50}
        quantity={2}
        onUpdateQuantity={(id, qty) => console.log('Update quantity:', id, qty)}
        onRemove={(id) => console.log('Remove:', id)}
      />
    </div>
  );
}
