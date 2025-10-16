import { useState } from "react";
import { InventoryRow } from "@/components/inventory-row";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Search, Plus } from "lucide-react";

//todo: remove mock functionality
const mockInventory = [
  { id: 1, name: "Espresso Beans", category: "Coffee", stock: 45, minStock: 20, price: 24.99 },
  { id: 2, name: "Milk", category: "Dairy", stock: 8, minStock: 10, price: 3.50 },
  { id: 3, name: "Sugar", category: "Ingredients", stock: 3, minStock: 5, price: 2.99 },
  { id: 4, name: "Croissants", category: "Pastry", stock: 15, minStock: 10, price: 2.50 },
  { id: 5, name: "Coffee Cups (12oz)", category: "Supplies", stock: 120, minStock: 50, price: 15.00 },
  { id: 6, name: "Chocolate Syrup", category: "Ingredients", stock: 25, minStock: 10, price: 8.99 },
];

export default function Inventory() {
  const [inventory, setInventory] = useState(mockInventory);
  const [searchQuery, setSearchQuery] = useState("");

  const handleUpdate = (id: number, newStock: number) => {
    setInventory(inventory.map(item => 
      item.id === id ? { ...item, stock: newStock } : item
    ));
  };

  const filteredInventory = inventory.filter(item =>
    item.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    item.category.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold">Inventory Management</h1>
          <p className="text-muted-foreground">Track and manage stock levels</p>
        </div>
        <Button data-testid="button-add-product">
          <Plus className="h-4 w-4 mr-2" />
          Add Product
        </Button>
      </div>

      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          placeholder="Search inventory..."
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          className="pl-9"
          data-testid="input-search-inventory"
        />
      </div>

      <div className="border rounded-lg">
        <table className="w-full">
          <thead className="bg-muted/50">
            <tr className="border-b">
              <th className="p-4 text-left text-sm font-medium">Product</th>
              <th className="p-4 text-left text-sm font-medium">Category</th>
              <th className="p-4 text-left text-sm font-medium">Stock</th>
              <th className="p-4 text-left text-sm font-medium">Price</th>
              <th className="p-4 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredInventory.map((item) => (
              <InventoryRow key={item.id} {...item} onUpdate={handleUpdate} />
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
