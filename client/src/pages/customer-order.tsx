import { useState } from "react";
import { Button } from "@/components/ui/button";
import { ProductCard } from "@/components/product-card";
import { CartItem } from "@/components/cart-item";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { ShoppingBag, MapPin } from "lucide-react";

import heroImg from "@assets/generated_images/Coffee_shop_hero_image_0c68654c.png";
import espressoImg from "@assets/stock_images/espresso_coffee_cup_c9430f9c.jpg";
import cappuccinoImg from "@assets/stock_images/cappuccino_coffee_01eb54f6.jpg";
import icedLatteImg from "@assets/stock_images/iced_latte_coffee_23914aaf.jpg";
import croissantImg from "@assets/stock_images/croissant_pastry_24f5c524.jpg";

//todo: remove mock functionality
const mockProducts = [
  { id: 1, name: "Espresso", price: 3.50, image: espressoImg, category: "Coffee" },
  { id: 2, name: "Cappuccino", price: 4.50, image: cappuccinoImg, category: "Coffee" },
  { id: 3, name: "Iced Latte", price: 5.00, image: icedLatteImg, category: "Coffee" },
  { id: 4, name: "Croissant", price: 3.50, image: croissantImg, category: "Pastry" },
];

export default function CustomerOrder() {
  const [cart, setCart] = useState<Array<{ id: number; name: string; price: number; quantity: number }>>([]);
  const [showCheckout, setShowCheckout] = useState(false);

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

  const handlePlaceOrder = () => {
    console.log('Order placed:', cart);
    setCart([]);
    setShowCheckout(false);
  };

  return (
    <div className="min-h-screen bg-background">
      <div className="relative h-96 overflow-hidden">
        <img 
          src={heroImg} 
          alt="Bro's Cafe" 
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-black/20" />
        <div className="absolute inset-0 flex items-center justify-center">
          <div className="text-center text-white">
            <h1 className="font-serif text-5xl font-bold mb-4">Bro's Cafe</h1>
            <p className="text-xl">Order your favorites online</p>
            <Badge className="mt-4 bg-primary border-primary-border">
              Free delivery on orders over $20
            </Badge>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto p-6">
        <div className="flex items-center gap-2 mb-6 text-muted-foreground">
          <MapPin className="h-4 w-4" />
          <span className="text-sm">Delivering to your area</span>
        </div>

        <div className="grid md:grid-cols-3 gap-8">
          <div className="md:col-span-2">
            <h2 className="text-2xl font-bold mb-6">Our Menu</h2>
            <div className="grid gap-4 sm:grid-cols-2">
              {mockProducts.map((product) => (
                <ProductCard key={product.id} {...product} onAddToCart={addToCart} />
              ))}
            </div>
          </div>

          <div>
            <Card className="sticky top-6">
              <CardHeader className="flex flex-row items-center justify-between gap-2 space-y-0">
                <CardTitle className="flex items-center gap-2">
                  <ShoppingBag className="h-5 w-5" />
                  Your Order
                </CardTitle>
                <Badge>{cart.length}</Badge>
              </CardHeader>
              <CardContent className="space-y-4">
                {cart.length === 0 ? (
                  <p className="text-muted-foreground text-center py-4">Your cart is empty</p>
                ) : (
                  <>
                    <div className="space-y-2 max-h-64 overflow-auto">
                      {cart.map((item) => (
                        <CartItem
                          key={item.id}
                          {...item}
                          onUpdateQuantity={updateQuantity}
                          onRemove={removeFromCart}
                        />
                      ))}
                    </div>

                    <div className="pt-4 border-t space-y-3">
                      <div className="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span data-testid="customer-cart-total">${total.toFixed(2)}</span>
                      </div>

                      {!showCheckout ? (
                        <Button 
                          className="w-full" 
                          onClick={() => setShowCheckout(true)}
                          data-testid="button-proceed-checkout"
                        >
                          Proceed to Checkout
                        </Button>
                      ) : (
                        <div className="space-y-3">
                          <div className="space-y-2">
                            <Label htmlFor="customer-name">Name</Label>
                            <Input id="customer-name" placeholder="Your name" data-testid="input-customer-name" />
                          </div>
                          <div className="space-y-2">
                            <Label htmlFor="customer-address">Delivery Address</Label>
                            <Input id="customer-address" placeholder="123 Main St" data-testid="input-customer-address" />
                          </div>
                          <div className="space-y-2">
                            <Label htmlFor="customer-phone">Phone</Label>
                            <Input id="customer-phone" placeholder="+1 234 567 8900" data-testid="input-customer-phone" />
                          </div>
                          <Button 
                            className="w-full" 
                            onClick={handlePlaceOrder}
                            data-testid="button-place-order"
                          >
                            Place Order
                          </Button>
                        </div>
                      )}
                    </div>
                  </>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
}
