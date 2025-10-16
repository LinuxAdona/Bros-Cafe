import { OrderCard } from '../order-card';

export default function OrderCardExample() {
  return (
    <div className="p-6 max-w-sm">
      <OrderCard
        id={1}
        orderNumber="1234"
        customerName="John Doe"
        items={["1x Espresso", "1x Croissant"]}
        total={8.50}
        status="preparing"
        timestamp="10 mins ago"
        onStatusChange={(id, status) => console.log('Status changed:', id, status)}
      />
    </div>
  );
}
