import { useState } from "react";
import { ProductCard } from "@/components/product-card";
import { CartItem } from "@/components/cart-item";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Search, CreditCard, Banknote } from "lucide-react";

import espressoImg from "@assets/stock_images/espresso_coffee_cup_c9430f9c.jpg";
import cappuccinoImg from "@assets/stock_images/cappuccino_coffee_01eb54f6.jpg";
import icedLatteImg from "@assets/stock_images/iced_latte_coffee_23914aaf.jpg";
import croissantImg from "@assets/stock_images/croissant_pastry_24f5c524.jpg";

//todo: remove mock functionality
const mockProducts = [
  { id: 1, name: "Espresso", price: 3.50, image: espressoImg, category: "Coffee", stock: 45 },
  { id: 2, name: "Cappuccino", price: 4.50, image: cappuccinoImg, category: "Coffee", stock: 32 },
  { id: 3, name: "Iced Latte", price: 5.00, image: icedLatteImg, category: "Coffee", stock: 28 },
  { id: 4, name: "Croissant", price: 3.50, image: croissantImg, category: "Pastry", stock: 15 },
  { id: 5, name: "Americano", price: 3.00, image: espressoImg, category: "Coffee", stock: 40 },
  { id: 6, name: "Muffin", price: 3.00, image: croissantImg, category: "Pastry", stock: 20 },
];

export default function POS() {
  const [cart, setCart] = useState<Array<{ id: number; name: string; price: number; quantity: number }>>([]);
  const [searchQuery, setSearchQuery] = useState("");

  const filteredProducts = mockProducts.filter(p => 
    p.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const addToCart = (id: number) => {
    const product = mockProducts.find(p => p.id === id);
    if (!product) return;

    const existingItem = cart.find(item => item.id === id);
    if (existingItem) {
      setCart(cart.map(item => 
        item.id === id ? { ...item, quantity: item.quantity + 1 } : item
      ));
    } else {
      setCart([...cart, { id: product.id, name: product.name, price: product.price, quantity: 1 }]);
    }
  };

  const updateQuantity = (id: number, quantity: number) => {
    if (quantity <= 0) return;
    setCart(cart.map(item => item.id === id ? { ...item, quantity } : item));
  };

  const removeFromCart = (id: number) => {
    setCart(cart.filter(item => item.id !== id));
  };

  const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

  const handleCheckout = () => {
    console.log('Processing checkout:', cart, 'Total:', total);
    setCart([]);
  };

  return (
    <div className="flex h-full">
      <div className="flex-1 p-6 overflow-auto">
        <div className="mb-6">
          <h1 className="text-3xl font-bold mb-2">Point of Sale</h1>
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Search products..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-9"
              data-testid="input-search-products"
            />
          </div>
        </div>

        <Tabs defaultValue="all" className="space-y-4">
          <TabsList>
            <TabsTrigger value="all" data-testid="tab-all">All</TabsTrigger>
            <TabsTrigger value="coffee" data-testid="tab-coffee">Coffee</TabsTrigger>
            <TabsTrigger value="pastry" data-testid="tab-pastry">Pastries</TabsTrigger>
          </TabsList>

          <TabsContent value="all" className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {filteredProducts.map((product) => (
                <ProductCard key={product.id} {...product} onAddToCart={addToCart} />
              ))}
            </div>
          </TabsContent>

          <TabsContent value="coffee" className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {filteredProducts.filter(p => p.category === "Coffee").map((product) => (
                <ProductCard key={product.id} {...product} onAddToCart={addToCart} />
              ))}
            </div>
          </TabsContent>

          <TabsContent value="pastry" className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {filteredProducts.filter(p => p.category === "Pastry").map((product) => (
                <ProductCard key={product.id} {...product} onAddToCart={addToCart} />
              ))}
            </div>
          </TabsContent>
        </Tabs>
      </div>

      <div className="w-96 border-l bg-card p-6 flex flex-col">
        <h2 className="text-xl font-bold mb-4">Current Order</h2>
        
        <div className="flex-1 overflow-auto">
          {cart.length === 0 ? (
            <p className="text-muted-foreground text-center py-8">Cart is empty</p>
          ) : (
            cart.map((item) => (
              <CartItem
                key={item.id}
                {...item}
                onUpdateQuantity={updateQuantity}
                onRemove={removeFromCart}
              />
            ))
          )}
        </div>

        <div className="pt-4 border-t space-y-4">
          <div className="flex justify-between text-lg font-bold">
            <span>Total</span>
            <span data-testid="cart-total">${total.toFixed(2)}</span>
          </div>

          <div className="grid grid-cols-2 gap-2">
            <Button 
              variant="outline" 
              className="w-full"
              onClick={handleCheckout}
              disabled={cart.length === 0}
              data-testid="button-checkout-cash"
            >
              <Banknote className="h-4 w-4 mr-2" />
              Cash
            </Button>
            <Button 
              className="w-full"
              onClick={handleCheckout}
              disabled={cart.length === 0}
              data-testid="button-checkout-card"
            >
              <CreditCard className="h-4 w-4 mr-2" />
              Card
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}
