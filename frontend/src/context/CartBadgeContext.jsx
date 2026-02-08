import { createContext, useContext, useEffect, useMemo, useState } from "react";

const CartBadgeContext = createContext(null);

export function CartBadgeProvider({ children }) {
  const [cartLines, setCartLines] = useState([]);
  const [cartRestaurantSlug, setCartRestaurantSlug] = useState(null);
  const [minimumOrder] = useState(20);

  const cartCount = useMemo(
    () => cartLines.reduce((sum, line) => sum + line.quantity, 0),
    [cartLines],
  );

  const addLine = ({ restaurantSlug, line }) => {
    if (!line) {
      return;
    }

    setCartRestaurantSlug((prev) => prev ?? restaurantSlug ?? null);
    setCartLines((prev) => {
      const existingIndex = prev.findIndex(
        (item) => item.productId === line.productId && item.signature === line.signature,
      );

      if (existingIndex === -1) {
        return [...prev, { ...line, quantity: Math.max(1, line.quantity || 1) }];
      }

      return prev.map((item, index) =>
        index === existingIndex
          ? { ...item, quantity: item.quantity + Math.max(1, line.quantity || 1) }
          : item,
      );
    });
  };

  const replaceCartWithLine = ({ restaurantSlug, line }) => {
    if (!line) {
      return;
    }
    setCartRestaurantSlug(restaurantSlug ?? null);
    setCartLines([{ ...line, quantity: Math.max(1, line.quantity || 1) }]);
  };

  const updateLineQuantity = (lineId, nextQuantity) => {
    if (nextQuantity <= 0) {
      setCartLines((prev) => prev.filter((line) => line.id !== lineId));
      return;
    }
    setCartLines((prev) =>
      prev.map((line) =>
        line.id === lineId ? { ...line, quantity: Math.max(1, nextQuantity) } : line,
      ),
    );
  };

  const removeLine = (lineId) => {
    setCartLines((prev) => prev.filter((line) => line.id !== lineId));
  };

  const clear = () => {
    setCartLines([]);
    setCartRestaurantSlug(null);
  };

  const increment = () => {
    setCartLines((prev) => {
      if (prev.length === 0) {
        return prev;
      }
      return prev.map((line, index) =>
        index === 0 ? { ...line, quantity: line.quantity + 1 } : line,
      );
    });
  };

  const value = useMemo(() => {
    const subtotal = cartLines.reduce((sum, line) => sum + line.unitTotal * line.quantity, 0);
    return {
      cartCount,
      cartLines,
      cartRestaurantSlug,
      minimumOrder,
      subtotal,
      setCartRestaurantSlug,
      addLine,
      replaceCartWithLine,
      updateLineQuantity,
      removeLine,
      increment,
      clear,
    };
  }, [cartCount, cartLines, cartRestaurantSlug, minimumOrder]);

  useEffect(() => {
    if (cartLines.length === 0 && cartRestaurantSlug) {
      setCartRestaurantSlug(null);
    }
  }, [cartLines, cartRestaurantSlug]);

  return (
    <CartBadgeContext.Provider value={value}>
      {children}
    </CartBadgeContext.Provider>
  );
}

export function useCartBadge() {
  const context = useContext(CartBadgeContext);
  if (!context) {
    throw new Error("useCartBadge must be used inside CartBadgeProvider");
  }
  return context;
}
