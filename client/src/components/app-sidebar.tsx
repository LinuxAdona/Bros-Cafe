import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarHeader,
} from "@/components/ui/sidebar";
import { LayoutDashboard, ShoppingCart, Package, Users, ClipboardList, BarChart3, Settings } from "lucide-react";
import { Link, useLocation } from "wouter";

const menuItems = [
  { title: "Dashboard", icon: LayoutDashboard, path: "/" },
  { title: "POS", icon: ShoppingCart, path: "/pos" },
  { title: "Orders", icon: ClipboardList, path: "/orders" },
  { title: "Inventory", icon: Package, path: "/inventory" },
  { title: "Analytics", icon: BarChart3, path: "/analytics" },
  { title: "Employees", icon: Users, path: "/employees" },
  { title: "Settings", icon: Settings, path: "/settings" },
];

export function AppSidebar() {
  const [location] = useLocation();

  return (
    <Sidebar>
      <SidebarHeader className="p-6 border-b">
        <h1 className="font-serif text-2xl font-bold">Bro's Cafe</h1>
        <p className="text-sm text-muted-foreground">Management System</p>
      </SidebarHeader>
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel>Main Menu</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              {menuItems.map((item) => (
                <SidebarMenuItem key={item.title}>
                  <SidebarMenuButton asChild isActive={location === item.path}>
                    <Link href={item.path} data-testid={`link-${item.title.toLowerCase()}`}>
                      <item.icon className="h-4 w-4" />
                      <span>{item.title}</span>
                    </Link>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
    </Sidebar>
  );
}
