import { InventoryRow } from '../inventory-row';

export default function InventoryRowExample() {
  return (
    <div className="p-6">
      <table className="w-full">
        <thead>
          <tr className="border-b">
            <th className="p-4 text-left text-sm font-medium">Product</th>
            <th className="p-4 text-left text-sm font-medium">Category</th>
            <th className="p-4 text-left text-sm font-medium">Stock</th>
            <th className="p-4 text-left text-sm font-medium">Price</th>
            <th className="p-4 text-left text-sm font-medium">Actions</th>
          </tr>
        </thead>
        <tbody>
          <InventoryRow
            id={1}
            name="Espresso Beans"
            category="Coffee"
            stock={5}
            minStock={10}
            price={24.99}
            onUpdate={(id, stock) => console.log('Updated:', id, stock)}
          />
        </tbody>
      </table>
    </div>
  );
}
